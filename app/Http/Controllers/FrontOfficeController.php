<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\RoomStay;
use App\Models\Guest;
use App\Models\RoomType;
use App\Models\RoomChange;
use App\Services\FrontOfficeService;
use App\Traits\HasPropertySettings;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RoomStayStatus;
use App\Enums\BookingSource;
use App\Enums\GuestIdType;
use App\Mail\BookingConfirmationMail;
use App\Mail\CheckInConfirmationMail;
use App\Mail\InvoiceMail;
use App\Mail\RefundNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class FrontOfficeController extends Controller
{
    use HasPropertySettings;

    protected $frontOfficeService;

    public function __construct(FrontOfficeService $frontOfficeService)
    {
        $this->frontOfficeService = $frontOfficeService;
    }

    /**
     * Display Front Office dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Get room statistics
        $totalRooms = $property->hotelRooms()->count();
        $occupiedRooms = $property->hotelRooms()->occupied()->count();
        $dirtyRooms = $property->hotelRooms()->dirty()->count();
        $maintenanceRooms = $property->hotelRooms()->needsMaintenance()->count();
        $availableRooms = $property->hotelRooms()->available()->with('roomType')->get();
        $availableRoomsCount = $availableRooms->count();

        // Get today's activities
        // 🔧 BUG FIX: Add pending check-ins (reservations scheduled for today that need check-in)
        $pendingCheckInToday = RoomStay::where('property_id', $property->id)
            ->pendingCheckInToday()
            ->with(['guest', 'hotelRoom'])
            ->orderBy('check_in_date')
            ->get();

        $checkingInToday = RoomStay::where('property_id', $property->id)
            ->checkingInToday()
            ->with(['guest', 'hotelRoom'])
            ->get();

        $checkingOutToday = RoomStay::where('property_id', $property->id)
            ->checkingOutToday()
            ->with(['guest', 'hotelRoom'])
            ->get();

        // Get pending checkout today (scheduled today, belum checkout)
        $pendingCheckoutToday = RoomStay::where('property_id', $property->id)
            ->pendingCheckoutToday()
            ->with(['guest', 'hotelRoom'])
            ->get();

        // Get OVERDUE checkout (sudah lewat waktu checkout!)
        $overdueCheckout = RoomStay::where('property_id', $property->id)
            ->overdueCheckout()
            ->with(['guest', 'hotelRoom'])
            ->get();

        $currentGuests = RoomStay::where('property_id', $property->id)
            ->active()
            ->with(['guest', 'hotelRoom', 'roomType'])
            ->get();

        // Get room status overview
        $roomsByStatus = $property->hotelRooms()
            ->select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('frontoffice.index', compact(
            'property',
            'totalRooms',
            'occupiedRooms',
            'dirtyRooms',
            'maintenanceRooms',
            'availableRooms',
            'availableRoomsCount',
            'pendingCheckInToday', // 🔧 BUG FIX: Add pending check-ins
            'checkingInToday',
            'checkingOutToday',
            'pendingCheckoutToday',
            'overdueCheckout',
            'currentGuests',
            'roomsByStatus'
        ));
    }

    /**
     * Display room grid/rack.
     */
    public function roomGrid()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $rooms = $property->hotelRooms()
            ->with(['roomType.pricingRule', 'currentStay.guest', 'assignedHousekeeper'])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        // Get active BAR level
        $barActive = $property->bar_active ?? 'bar_1';

        // Get all room types with pricing rules for breakfast option lookup
        $roomTypes = $property->roomTypes()
            ->with('pricingRule')
            ->get();

        return view('frontoffice.room-grid', compact('property', 'rooms', 'barActive', 'roomTypes'));
    }


    /**
     * Create reservation for future dates.
     */
    public function createReservation(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Get max check-in days advance setting
        $maxDaysAdvance = $this->getSetting('max_check_in_days_advance', 365);

        $validated = $request->validate([
            'hotel_room_id' => [
                'required',
                'exists:hotel_rooms,id',
                Rule::exists('hotel_rooms', 'id')->where('property_id', $property->id),
            ],
            'check_in_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . now()->addDays($maxDaysAdvance)->format('Y-m-d'),
            ],
            'check_out_date' => 'required|date|after:check_in_date',
            'room_rate_per_night' => 'required|numeric|min:0|max:999999999',
            'adults' => 'required|integer|min:1|max:20',
            'children' => 'nullable|integer|min:0|max:20',
            'source' => ['required', Rule::in(BookingSource::values())],
            'ota_name' => 'nullable|string|max:255',
            'ota_booking_id' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
            // Deposit
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_paid' => 'nullable|numeric|min:0|lte:deposit_amount',
            'payment_method' => 'nullable|required_with:deposit_paid|in:cash,credit_card,debit_card,bank_transfer,other',
            // Guest information
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'nullable|string|max:255',
            'guest.email' => 'nullable|email|max:255',
            'guest.phone' => 'required|string|max:20',
            'guest.id_type' => ['required', Rule::in(GuestIdType::values())],
            'guest.id_number' => 'required|string|max:50',
            'guest.address' => 'nullable|string|max:500',
            'guest.city' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Validate guest ID format if strict validation is enabled
            if ($this->getSetting('require_guest_id_validation', true)) {
                $idType = GuestIdType::from($validated['guest']['id_type']);
                if (!$idType->validate($validated['guest']['id_number'])) {
                    throw new \Exception("Format {$idType->label()} tidak valid.");
                }
            }

            // Calculate charges using settings
            $room = HotelRoom::findOrFail($validated['hotel_room_id']);
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);

            $totalRoomCharge = $validated['room_rate_per_night'] * $nights;
            $taxAmount = $this->calculateTax($totalRoomCharge);
            $serviceCharge = $this->calculateServiceCharge($totalRoomCharge);

            // Get current BAR level from property
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

            $data = array_merge($validated, [
                'property_id' => $property->id,
                'total_room_charge' => $totalRoomCharge,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'bar_level' => $barLevel,
                'status' => RoomStayStatus::RESERVED->value,
            ]);

            $roomStay = $this->frontOfficeService->createReservation($data);

            // Record deposit payment if provided
            if (!empty($validated['deposit_paid']) && $validated['deposit_paid'] > 0) {
                $depositPayment = Payment::create([
                    'property_id' => $property->id,
                    'payment_number' => 'DEP-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'payable_type' => RoomStay::class,
                    'payable_id' => $roomStay->id,
                    'payment_method' => $validated['payment_method'],
                    'amount' => $validated['deposit_paid'],
                    'status' => 'completed',
                    'payment_date' => now(),
                    'notes' => 'Deposit untuk reservasi ' . $roomStay->confirmation_number,
                    'processed_by' => auth()->id(),
                ]);

                // ✅ SECURITY FIX: Validate payment was created successfully
                if (!$depositPayment) {
                    DB::rollBack();
                    throw new \Exception('Gagal membuat record pembayaran deposit.');
                }

                // Update room stay paid_amount
                $roomStay->increment('paid_amount', $validated['deposit_paid']);
                $roomStay->updatePaymentStatus();
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'action' => 'create',
                'description' => auth()->user()->name . " membuat reservasi untuk tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, check-in: " . $roomStay->check_in_date->format('d/m/Y') . ", check-out: " . $roomStay->check_out_date->format('d/m/Y') . ", konfirmasi: {$roomStay->confirmation_number}",
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Send booking confirmation email
            if ($roomStay->guest->email) {
                try {
                    // Convert to Reservation model for email
                    $reservation = \App\Models\Reservation::where('room_stay_id', $roomStay->id)->first();
                    if ($reservation) {
                        Mail::to($roomStay->guest->email)->send(new BookingConfirmationMail($reservation));
                    }
                } catch (\Exception $emailException) {
                    // Log email error but don't fail the request
                    Log::error('Failed to send booking confirmation email', [
                        'room_stay_id' => $roomStay->id,
                        'guest_email' => $roomStay->guest->email,
                        'error' => $emailException->getMessage(),
                    ]);
                }
            }

            $successMessage = "✅ RESERVASI BERHASIL DIBUAT!\n\n" .
                              "Confirmation Number: {$roomStay->confirmation_number}\n" .
                              "Tamu: {$roomStay->guest->full_name}\n" .
                              "Kamar: {$roomStay->hotelRoom->room_number}\n" .
                              "Check-in: " . $roomStay->check_in_date->format('d M Y') . "\n" .
                              "Check-out: " . $roomStay->check_out_date->format('d M Y') . "\n" .
                              "Status: Reserved\n\n" .
                              "📅 Lihat di kalender: /property/calendar";

            return redirect()->route('frontoffice.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create reservation', [
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated,
            ]);

            // Get specific error message
            $errorMessage = $e->getMessage();

            // Add more context to error message
            if (str_contains($errorMessage, 'sudah memiliki reservasi')) {
                $errorMessage = '❌ DOUBLE BOOKING TERDETEKSI! ' . $errorMessage;
            } elseif (str_contains($errorMessage, 'not available')) {
                $errorMessage = '❌ Kamar tidak tersedia untuk check-in.';
            } else {
                $errorMessage = '❌ Gagal membuat reservasi: ' . $errorMessage;
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Process check-in.
     */
    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $validated = $request->validate([
            'hotel_room_id' => [
                'required',
                'exists:hotel_rooms,id',
                Rule::exists('hotel_rooms', 'id')->where('property_id', $property->id),
            ],
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_rate_per_night' => 'required|numeric|min:0|max:999999999',
            'with_breakfast' => 'required|boolean',
            'adults' => 'required|integer|min:1|max:20',
            'children' => 'nullable|integer|min:0|max:20',
            'source' => ['required', Rule::in(BookingSource::values())],
            'ota_name' => 'nullable|string|max:255',
            'ota_booking_id' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
            // Deposit
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_paid' => 'nullable|numeric|min:0|lte:deposit_amount',
            'deposit_payment_method' => 'nullable|required_with:deposit_paid|in:cash,credit_card,debit_card,bank_transfer,other',
            // Guest information
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'nullable|string|max:255',
            'guest.email' => 'nullable|email|max:255',
            'guest.phone' => 'required|string|max:20',
            'guest.id_type' => ['required', Rule::in(GuestIdType::values())],
            'guest.id_number' => 'required|string|max:50',
            'guest.address' => 'nullable|string|max:500',
            'guest.city' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Validate guest ID format if strict validation is enabled
            if ($this->getSetting('require_guest_id_validation', true)) {
                $idType = GuestIdType::from($validated['guest']['id_type']);
                if (!$idType->validate($validated['guest']['id_number'])) {
                    throw new \Exception("Format {$idType->label()} tidak valid.");
                }
            }

            // Calculate charges using settings
            $room = HotelRoom::findOrFail($validated['hotel_room_id']);
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);

            $totalRoomCharge = $validated['room_rate_per_night'] * $nights;
            $taxAmount = $this->calculateTax($totalRoomCharge);
            $serviceCharge = $this->calculateServiceCharge($totalRoomCharge);

            // Calculate breakfast charges from settings
            $breakfastRate = $this->getBreakfastRate();
            $totalBreakfastCharge = 0;
            if ($validated['with_breakfast']) {
                $totalGuests = $validated['adults'] + ($validated['children'] ?? 0);
                $totalBreakfastCharge = $breakfastRate * $totalGuests * $nights;
            }

            // Get current BAR level from property
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

            $data = array_merge($validated, [
                'property_id' => $property->id,
                'total_room_charge' => $totalRoomCharge,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'breakfast_rate' => $breakfastRate,
                'total_breakfast_charge' => $totalBreakfastCharge,
                'bar_level' => $barLevel,
            ]);

            $roomStay = $this->frontOfficeService->checkIn($data);

            // Record deposit payment if provided
            if (!empty($validated['deposit_paid']) && $validated['deposit_paid'] > 0) {
                $depositPayment = Payment::create([
                    'property_id' => $property->id,
                    'payment_number' => 'DEP-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'payable_type' => RoomStay::class,
                    'payable_id' => $roomStay->id,
                    'payment_method' => $validated['deposit_payment_method'],
                    'amount' => $validated['deposit_paid'],
                    'status' => 'completed',
                    'payment_date' => now(),
                    'notes' => 'Deposit saat check-in untuk ' . $roomStay->confirmation_number,
                    'processed_by' => auth()->id(),
                ]);

                // ✅ SECURITY FIX: Validate payment was created successfully
                if (!$depositPayment) {
                    DB::rollBack();
                    throw new \Exception('Gagal membuat record pembayaran deposit.');
                }

                // Update room stay paid_amount
                $roomStay->increment('paid_amount', $validated['deposit_paid']);
                $roomStay->updatePaymentStatus();
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'action' => 'create',
                'description' => auth()->user()->name . " melakukan check-in tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, dari: " . $roomStay->actual_check_in->format('d/m/Y H:i') . ", sampai: " . $roomStay->check_out_date->format('d/m/Y') . ", konfirmasi: {$roomStay->confirmation_number}",
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Send check-in confirmation email
            if ($roomStay->guest->email) {
                try {
                    Mail::to($roomStay->guest->email)->send(new CheckInConfirmationMail($roomStay));
                } catch (\Exception $emailException) {
                    // Log email error but don't fail the request
                    Log::error('Failed to send check-in confirmation email', [
                        'room_stay_id' => $roomStay->id,
                        'guest_email' => $roomStay->guest->email,
                        'error' => $emailException->getMessage(),
                    ]);
                }
            }

            return redirect()->route('frontoffice.index')
                ->with('success', "Check-in berhasil! Confirmation Number: {$roomStay->confirmation_number}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to check-in', [
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan check-in. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * 🔧 BUG FIX: Verify and process check-in for an existing reservation.
     * This allows front desk to check in guests who have made reservations.
     */
    public function verifyCheckIn(Request $request, RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke reservasi ini.');
        }

        // Validate reservation is in 'reserved' status
        if ($roomStay->status !== RoomStayStatus::RESERVED->value) {
            return redirect()->back()->with('error', 'Reservasi ini tidak dapat di-check-in. Status saat ini: ' . $roomStay->status);
        }

        try {
            DB::beginTransaction();

            // Update room stay status to checked_in
            $roomStay->update([
                'status' => RoomStayStatus::CHECKED_IN->value,
                'actual_check_in' => now(),
            ]);

            // Update room status to occupied
            $room = $roomStay->hotelRoom;
            $room->update(['status' => 'occupied']);

            // Update daily occupancy
            $this->frontOfficeService->updateDailyOccupancy(
                $property->id,
                $roomStay->check_in_date
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'action' => 'checkin',
                'description' => auth()->user()->name . " melakukan check-in untuk tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, konfirmasi: {$roomStay->confirmation_number}",
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('frontoffice.folio', $roomStay)
                ->with('success', 'Check-in berhasil! Tamu ' . $roomStay->guest->full_name . ' telah check-in di kamar ' . $roomStay->hotelRoom->room_number);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during verify check-in: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal melakukan check-in. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Show check-out payment page.
     */
    public function checkOut(RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke room stay ini.');
        }

        // Validate room stay status - prevent access if already checked out
        if ($roomStay->status === RoomStayStatus::CHECKED_OUT->value) {
            return redirect()->route('frontoffice.index')
                ->with('error', '❌ Tamu ini sudah checkout pada ' . $roomStay->actual_check_out->format('d M Y H:i') . '. Tidak dapat melakukan checkout ulang.');
        }

        // Validate room stay can be checked out
        if (!in_array($roomStay->status, [RoomStayStatus::CHECKED_IN->value, RoomStayStatus::RESERVED->value])) {
            return redirect()->route('frontoffice.index')
                ->with('error', '❌ Room stay dengan status "' . $roomStay->status . '" tidak dapat di-checkout. Hanya tamu dengan status "Checked In" atau "Reserved" yang bisa checkout.');
        }

        // Eager load relationships to prevent N+1 queries
        $roomStay->load([
            'guest',
            'hotelRoom.roomType',
            'fnbOrders' => function ($query) {
                $query->with('items.menuItem');
            },
            'payments'
        ]);

        return view('frontoffice.checkout-payment', compact('roomStay'));
    }

    /**
     * Process check-out with payment.
     */
    public function processCheckout(Request $request, RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke room stay ini.');
        }

        // Validate room stay status
        if ($roomStay->status !== RoomStayStatus::CHECKED_IN->value) {
            return redirect()->back()
                ->with('error', 'Room stay harus dalam status checked-in untuk melakukan checkout.');
        }

        $validated = $request->validate([
            'payments' => 'nullable|array|min:0|max:10',
            'payments.*.payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'payments.*.amount' => 'required|numeric|min:0|max:999999999',
            'payments.*.card_number_last4' => 'nullable|string|size:4|regex:/^[0-9]{4}$/',
            'payments.*.card_holder_name' => 'nullable|string|max:255',
            'payments.*.card_type' => 'nullable|string|max:50',
            'payments.*.bank_name' => 'nullable|string|max:255',
            'payments.*.reference_number' => 'nullable|string|max:255',
            'payments.*.notes' => 'nullable|string|max:500',

            // Refund fields
            'refund_method' => 'nullable|in:cash,bank_transfer,credit_card,debit_card,other',
            'refund_bank_name' => 'nullable|string|max:255',
            'refund_account_number' => 'nullable|string|max:255',
            'refund_account_holder' => 'nullable|string|max:255',
            'refund_notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total bill using aggregate query to avoid N+1
            $fnbTotal = \App\Models\FnbOrder::where('room_stay_id', $roomStay->id)
                ->sum('total_amount');

            $totalBill = round(
                $roomStay->total_room_charge
                + $roomStay->total_breakfast_charge
                + $fnbTotal
                + $roomStay->tax_amount
                + $roomStay->service_charge
                - ($roomStay->discount_amount ?? 0),
                2
            );

            // Calculate balance due (considering deposits already paid)
            $depositPaid = $roomStay->paid_amount ?? 0;
            $balanceDue = round($totalBill - $depositPaid, 2);

            // Calculate total payment amount now
            $totalPaid = round(collect($validated['payments'] ?? [])->sum('amount'), 2);

            // Get payment tolerance from settings (in smallest currency unit)
            $paymentTolerance = $this->getPaymentTolerance();

            // Handle refund scenario (deposit > total bill)
            $refundAmount = 0;
            $refundRecord = null;

            if ($balanceDue < 0) {
                // Refund scenario
                $refundAmount = abs($balanceDue);

                // Payment should be zero or minimal in refund scenario
                if ($totalPaid > $paymentTolerance) {
                    throw new \Exception(
                        "Tamu tidak perlu membayar tambahan karena deposit melebihi tagihan. " .
                        "Refund yang harus diberikan: Rp " . number_format($refundAmount, 0, ',', '.')
                    );
                }

                // Validate refund method is provided
                if (empty($validated['refund_method'])) {
                    throw new \Exception("Metode refund harus dipilih karena deposit melebihi tagihan.");
                }

                // Create refund record
                $refundRecord = \App\Models\Refund::create([
                    'property_id' => $property->id,
                    'room_stay_id' => $roomStay->id,
                    'original_payment_id' => $roomStay->payments()->where('notes', 'like', '%Deposit%')->first()?->id,
                    'amount' => $refundAmount,
                    'refund_method' => $validated['refund_method'],
                    'status' => 'pending',
                    'reason' => 'Deposit melebihi total tagihan',
                    'notes' => $validated['refund_notes'] ?? null,
                    'bank_name' => $validated['refund_bank_name'] ?? null,
                    'account_number' => $validated['refund_account_number'] ?? null,
                    'account_holder_name' => $validated['refund_account_holder'] ?? null,
                    'processed_by' => null, // Will be processed later
                    'processed_at' => null,
                ]);

            } else {
                // Normal payment scenario
                // Strict validation: payment must match balance due within tolerance
                $difference = abs($totalPaid - $balanceDue);

                if ($difference > $paymentTolerance) {
                    throw new \Exception(
                        "Total pembayaran tidak sesuai dengan saldo yang harus dibayar. " .
                        "Total Tagihan: Rp " . number_format($totalBill, 0, ',', '.') . ", " .
                        "Deposit Terbayar: Rp " . number_format($depositPaid, 0, ',', '.') . ", " .
                        "Saldo Due: Rp " . number_format($balanceDue, 0, ',', '.') . ", " .
                        "Dibayar Sekarang: Rp " . number_format($totalPaid, 0, ',', '.') . ", " .
                        "Selisih: Rp " . number_format($difference, 0, ',', '.')
                    );
                }
            }

            // Validate card info is provided for card payments
            foreach ($validated['payments'] ?? [] as $index => $paymentData) {
                $paymentMethod = PaymentMethod::from($paymentData['payment_method']);

                if ($paymentMethod->requiresCardInfo()) {
                    if (empty($paymentData['card_number_last4'])) {
                        throw new \Exception("4 digit terakhir kartu diperlukan untuk pembayaran {$paymentMethod->label()}.");
                    }
                    if (empty($paymentData['card_holder_name'])) {
                        throw new \Exception("Nama pemegang kartu diperlukan untuk pembayaran {$paymentMethod->label()}.");
                    }
                }
            }

            // Create payment records (if any payment is made)
            $paymentMethods = [];
            if (!empty($validated['payments'])) {
                foreach ($validated['payments'] as $paymentData) {
                    // Skip creating payment if amount is 0 (already paid via deposit)
                    if ($paymentData['amount'] <= 0) {
                        continue;
                    }

                    $payment = \App\Models\Payment::create([
                        'property_id' => $property->id,
                        'payable_id' => $roomStay->id,
                        'payable_type' => \App\Models\RoomStay::class,
                        'payment_method' => $paymentData['payment_method'],
                        'amount' => $paymentData['amount'],
                        'card_number_last4' => $paymentData['card_number_last4'] ?? null,
                        'card_holder_name' => $paymentData['card_holder_name'] ?? null,
                        'card_type' => $paymentData['card_type'] ?? null,
                        'bank_name' => $paymentData['bank_name'] ?? null,
                        'reference_number' => $paymentData['reference_number'] ?? null,
                        'notes' => $paymentData['notes'] ?? null,
                        'status' => PaymentStatus::COMPLETED->value,
                        'payment_date' => now(),
                        'processed_by' => $user->id,
                    ]);

                    // ✅ SECURITY FIX: Validate payment was created successfully
                    if (!$payment) {
                        DB::rollBack();
                        throw new \Exception('Gagal membuat record pembayaran.');
                    }

                    $paymentMethods[] = PaymentMethod::from($paymentData['payment_method'])->label();
                }
            }

            // Update room stay payment status
            $totalPaidIncludingDeposit = $depositPaid + $totalPaid;
            $roomStay->update([
                'payment_status' => 'paid',
                'paid_amount' => $totalPaidIncludingDeposit,
            ]);

            // Process checkout (marks room as dirty, updates status)
            $this->frontOfficeService->checkOut($roomStay);

            // Build activity log description
            $activityDescription = auth()->user()->name . " melakukan check-out tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, total tagihan: Rp " . number_format($totalBill, 0, ',', '.');

            if ($depositPaid > 0) {
                $activityDescription .= ", deposit terbayar: Rp " . number_format($depositPaid, 0, ',', '.');
            }

            if (!empty($paymentMethods)) {
                $activityDescription .= ", pembayaran checkout: " . implode(', ', $paymentMethods) . " (Rp " . number_format($totalPaid, 0, ',', '.') . ")";
            }

            if ($refundRecord) {
                $activityDescription .= ", REFUND: Rp " . number_format($refundAmount, 0, ',', '.') . " (" . $refundRecord->refund_method_label . ", #{$refundRecord->refund_number})";
            }

            $activityDescription .= ", konfirmasi: {$roomStay->confirmation_number}";

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'action' => 'update',
                'description' => $activityDescription,
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Send invoice email
            if ($roomStay->guest->email) {
                try {
                    Mail::to($roomStay->guest->email)->send(new InvoiceMail($roomStay));
                } catch (\Exception $emailException) {
                    // Log email error but don't fail the request
                    Log::error('Failed to send invoice email', [
                        'room_stay_id' => $roomStay->id,
                        'guest_email' => $roomStay->guest->email,
                        'error' => $emailException->getMessage(),
                    ]);
                }
            }

            // Send invoice via WhatsApp
            if ($roomStay->guest->phone) {
                try {
                    $communicationService = app(\App\Services\GuestCommunicationService::class);
                    $communicationService->sendInvoiceWhatsApp($roomStay);
                } catch (\Exception $whatsappException) {
                    // Log WhatsApp error but don't fail the request
                    Log::error('Failed to send invoice WhatsApp', [
                        'room_stay_id' => $roomStay->id,
                        'guest_phone' => $roomStay->guest->phone,
                        'error' => $whatsappException->getMessage(),
                    ]);
                }
            }

            // Send refund notification email if refund was created
            if ($refundRecord && $roomStay->guest->email) {
                try {
                    Mail::to($roomStay->guest->email)->send(new RefundNotificationMail($refundRecord));
                } catch (\Exception $emailException) {
                    // Log email error but don't fail the request
                    Log::error('Failed to send refund notification email', [
                        'refund_id' => $refundRecord->id,
                        'guest_email' => $roomStay->guest->email,
                        'error' => $emailException->getMessage(),
                    ]);
                }
            }

            $successMessage = "Check-out berhasil untuk kamar {$roomStay->hotelRoom->room_number}";
            if ($refundRecord) {
                $successMessage .= ". Refund sebesar Rp " . number_format($refundAmount, 0, ',', '.') . " perlu diproses ({$refundRecord->refund_number}).";
            }

            return redirect()->route('frontoffice.invoice', $roomStay)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process checkout', [
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'room_stay_id' => $roomStay->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Gagal melakukan check-out. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Print invoice for a room stay.
     */
    public function printInvoice(RoomStay $roomStay)
    {
        $roomStay->load(['guest', 'hotelRoom.roomType', 'property', 'fnbOrders.items.menuItem', 'payments']);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $roomStay->property_id,
            'action' => 'view',
            'description' => auth()->user()->name . " mencetak invoice untuk tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, konfirmasi: {$roomStay->confirmation_number}",
            'loggable_id' => $roomStay->id,
            'loggable_type' => RoomStay::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('frontoffice.invoice', compact('roomStay'));
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadInvoicePdf(RoomStay $roomStay)
    {
        $roomStay->load(['guest', 'hotelRoom.roomType', 'property', 'fnbOrders.items.menuItem', 'payments']);

        $pdf = \PDF::loadView('frontoffice.invoice-pdf', compact('roomStay'));

        $filename = 'invoice-' . $roomStay->confirmation_number . '.pdf';

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $roomStay->property_id,
            'action' => 'export',
            'description' => auth()->user()->name . " mengunduh invoice PDF untuk tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, konfirmasi: {$roomStay->confirmation_number}, file: {$filename}",
            'loggable_id' => $roomStay->id,
            'loggable_type' => RoomStay::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $pdf->download($filename);
    }

    /**
     * Mark room as clean (FO confirmation after housekeeping).
     */
    public function markRoomClean(HotelRoom $room)
    {
        try {
            // Change room status from vacant_dirty to vacant_clean
            if ($room->status !== 'vacant_dirty') {
                return redirect()->back()
                    ->with('error', 'Kamar ' . $room->room_number . ' tidak dalam status kotor.');
            }

            $room->update([
                'status' => 'vacant_clean',
                'last_cleaned_at' => now(),
                'last_cleaned_by' => auth()->id(),
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $room->property_id,
                'action' => 'update',
                'description' => auth()->user()->name . " menandai kamar {$room->room_number} sebagai bersih (vacant_clean)",
                'loggable_id' => $room->id,
                'loggable_type' => HotelRoom::class,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('frontoffice.room-grid')
                ->with('success', "Kamar {$room->room_number} telah ditandai sebagai sudah dibersihkan.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menandai kamar sebagai bersih: ' . $e->getMessage());
        }
    }

    /**
     * Search guest by name, phone, or email.
     */
    public function searchGuest(Request $request)
    {
        $query = $request->get('q');
        $user = auth()->user();
        $property = $user->property;

        $guests = Guest::where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->with(['roomStays' => function($q) use ($property) {
                $q->where('property_id', $property->id)
                  ->where('status', 'checked_in')
                  ->with('hotelRoom');
            }])
            ->limit(10)
            ->get()
            ->map(function($guest) {
                $currentStay = $guest->roomStays->first();
                return [
                    'id' => $guest->id,
                    'full_name' => $guest->full_name,
                    'phone' => $guest->phone,
                    'email' => $guest->email,
                    'current_stay' => $currentStay ? [
                        'room_number' => $currentStay->hotelRoom->room_number,
                        'check_in_date' => $currentStay->check_in_date->format('d M Y'),
                    ] : null,
                ];
            });

        return response()->json($guests);
    }

    /**
     * Show guest details.
     */
    public function showGuest(Guest $guest)
    {
        $guest->load(['roomStays.hotelRoom', 'roomStays.property', 'fnbOrders']);

        return view('frontoffice.guest-details', compact('guest'));
    }

    /**
     * Search for available rooms.
     */
    public function searchRooms(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $user = auth()->user();
        $property = $user->property;

        $availableRooms = $this->frontOfficeService->getAvailableRooms(
            $property->id,
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        return response()->json([
            'success' => true,
            'rooms' => $availableRooms,
        ]);
    }

    /**
     * Show extend stay form.
     */
    public function showExtendStay(RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke room stay ini.');
        }

        $roomStay->load(['guest', 'hotelRoom.roomType.pricingRule', 'property']);

        // Ensure room stay is active
        if ($roomStay->status !== RoomStayStatus::CHECKED_IN->value) {
            return redirect()->back()
                ->with('error', 'Hanya tamu yang sedang menginap yang bisa diperpanjang.');
        }

        return view('frontoffice.extend-stay', compact('roomStay'));
    }

    /**
     * Process extend stay.
     */
    public function extendStay(Request $request, RoomStay $roomStay)
    {
        $validated = $request->validate([
            'new_check_out_date' => 'required|date|after:' . $roomStay->check_out_date->format('Y-m-d'),
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $property = $user->property;

            // Calculate additional nights and charge
            $oldCheckOutDate = $roomStay->check_out_date;
            $newCheckOutDate = Carbon::parse($validated['new_check_out_date']);
            $additionalNights = $oldCheckOutDate->diffInDays($newCheckOutDate);
            $additionalCharge = $roomStay->room_rate_per_night * $additionalNights;
            $additionalTax = $this->calculateTax($additionalCharge);
            $additionalService = $this->calculateServiceCharge($additionalCharge);

            // Record the change
            RoomChange::create([
                'property_id' => $property->id,
                'room_stay_id' => $roomStay->id,
                'change_type' => 'extend_stay',
                'old_check_out_date' => $oldCheckOutDate,
                'new_check_out_date' => $newCheckOutDate,
                'old_rate' => $roomStay->room_rate_per_night,
                'new_rate' => $roomStay->room_rate_per_night,
                'additional_charge' => $additionalCharge + $additionalTax + $additionalService,
                'reason' => $validated['reason'] ?? 'Guest request',
                'notes' => $validated['notes'],
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            // Update room stay
            $roomStay->update([
                'check_out_date' => $newCheckOutDate,
                'total_room_charge' => $roomStay->total_room_charge + $additionalCharge,
                'tax_amount' => $roomStay->tax_amount + $additionalTax,
                'service_charge' => $roomStay->service_charge + $additionalService,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'update',
                'description' => $user->name . " memperpanjang stay tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, dari {$oldCheckOutDate->format('d/m/Y')} menjadi {$newCheckOutDate->format('d/m/Y')}, tambahan: Rp " . number_format($additionalCharge + $additionalTax + $additionalService, 0, ',', '.'),
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('frontoffice.index')
                ->with('success', "Stay berhasil diperpanjang sampai {$newCheckOutDate->format('d M Y')}. Tambahan biaya: Rp " . number_format($additionalCharge + $additionalTax + $additionalService, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperpanjang stay: ' . $e->getMessage());
        }
    }

    /**
     * Show change room form.
     */
    public function showChangeRoom(RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke room stay ini.');
        }

        $roomStay->load(['guest', 'hotelRoom.roomType', 'property']);

        // Ensure room stay is active
        if ($roomStay->status !== RoomStayStatus::CHECKED_IN->value) {
            return redirect()->back()
                ->with('error', 'Hanya tamu yang sedang menginap yang bisa pindah kamar.');
        }

        // Get available rooms
        $availableRooms = $property->hotelRooms()
            ->available()
            ->where('id', '!=', $roomStay->hotel_room_id)
            ->with('roomType.pricingRule')
            ->get();

        return view('frontoffice.change-room', compact('roomStay', 'availableRooms'));
    }

    /**
     * Process change room.
     */
    public function changeRoom(Request $request, RoomStay $roomStay)
    {
        $validated = $request->validate([
            'new_room_id' => 'required|exists:hotel_rooms,id',
            'new_rate' => 'required|numeric|min:0',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $property = $user->property;

            $oldRoom = $roomStay->hotelRoom;
            $newRoom = HotelRoom::findOrFail($validated['new_room_id']);

            // Check if new room is available
            if ($newRoom->status !== 'vacant_clean') {
                throw new \Exception("Kamar {$newRoom->room_number} tidak tersedia.");
            }

            // Calculate remaining nights (from today to checkout)
            $today = now()->startOfDay();
            $checkOutDate = $roomStay->check_out_date->startOfDay();

            // Ensure we don't get negative nights
            $remainingNights = max(0, $today->diffInDays($checkOutDate, false));

            if ($remainingNights <= 0) {
                throw new \Exception("Tidak ada malam tersisa untuk perubahan kamar. Check-out sudah dekat atau terlewat.");
            }

            $oldRoomCharge = $roomStay->room_rate_per_night * $remainingNights;
            $newRoomCharge = $validated['new_rate'] * $remainingNights;
            $additionalCharge = $newRoomCharge - $oldRoomCharge;

            // Record the change
            RoomChange::create([
                'property_id' => $property->id,
                'room_stay_id' => $roomStay->id,
                'old_room_id' => $oldRoom->id,
                'new_room_id' => $newRoom->id,
                'change_type' => 'room_change',
                'old_rate' => $roomStay->room_rate_per_night,
                'new_rate' => $validated['new_rate'],
                'additional_charge' => $additionalCharge,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            // Update old room status
            $oldRoom->update([
                'status' => 'vacant_dirty',
            ]);

            // Update new room status
            $newRoom->update([
                'status' => 'occupied',
            ]);

            // Update room stay
            $roomStay->update([
                'hotel_room_id' => $newRoom->id,
                'room_type_id' => $newRoom->room_type_id,
                'room_rate_per_night' => $validated['new_rate'],
            ]);

            // Recalculate total charges (for remaining nights)
            $totalNights = $roomStay->check_in_date->diffInDays($roomStay->check_out_date);
            $completedNights = $roomStay->actual_check_in->diffInDays(now());
            $newTotalCharge = ($roomStay->room_rate_per_night * $completedNights) + ($validated['new_rate'] * $remainingNights);

            $roomStay->update([
                'total_room_charge' => $newTotalCharge,
                'tax_amount' => $this->calculateTax($newTotalCharge),
                'service_charge' => $this->calculateServiceCharge($newTotalCharge),
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'update',
                'description' => $user->name . " memindahkan tamu {$roomStay->guest->full_name} dari kamar {$oldRoom->room_number} ke {$newRoom->room_number}, alasan: {$validated['reason']}, selisih biaya: Rp " . number_format($additionalCharge, 0, ',', '.'),
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('frontoffice.index')
                ->with('success', "Tamu berhasil dipindahkan ke kamar {$newRoom->room_number}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memindahkan kamar: ' . $e->getMessage());
        }
    }

    /**
     * Show group check-in form.
     */
    public function showGroupCheckIn()
    {
        $user = auth()->user();
        $property = $user->property;

        $availableRooms = $property->hotelRooms()
            ->available()
            ->with('roomType.pricingRule')
            ->get();

        $barActive = $property->bar_active ?? 'bar_1';

        return view('frontoffice.group-checkin', compact('property', 'availableRooms', 'barActive'));
    }

    /**
     * Process group check-in.
     */
    public function groupCheckIn(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'source' => ['required', Rule::in(BookingSource::values())],
            'special_requests' => 'nullable|string|max:1000',
            'rooms' => 'required|array|min:1|max:50',
            'rooms.*.hotel_room_id' => [
                'required',
                'exists:hotel_rooms,id',
                Rule::exists('hotel_rooms', 'id')->where('property_id', $property->id),
            ],
            'rooms.*.room_rate_per_night' => 'required|numeric|min:0|max:999999999',
            'rooms.*.adults' => 'required|integer|min:1|max:20',
            'rooms.*.children' => 'nullable|integer|min:0|max:20',
            'rooms.*.guest_first_name' => 'required|string|max:255',
            'rooms.*.guest_last_name' => 'nullable|string|max:255',
            'rooms.*.guest_email' => 'nullable|email|max:255',
            'rooms.*.guest_phone' => 'required|string|max:20',
            'rooms.*.guest_id_type' => ['required', Rule::in(GuestIdType::values())],
            'rooms.*.guest_id_number' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();
            $checkInDate = Carbon::parse($validated['check_in_date']);
            $checkOutDate = Carbon::parse($validated['check_out_date']);
            $nights = $checkInDate->diffInDays($checkOutDate);
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

            // ⚡ PERFORMANCE FIX: Eager load all rooms before loop to prevent N+1
            $roomIds = collect($validated['rooms'])->pluck('hotel_room_id')->toArray();
            $rooms = HotelRoom::whereIn('id', $roomIds)->get()->keyBy('id');

            $roomStays = [];
            $totalRooms = count($validated['rooms']);

            foreach ($validated['rooms'] as $roomData) {
                // Find or create guest
                $guest = $this->frontOfficeService->findOrCreateGuest([
                    'first_name' => $roomData['guest_first_name'],
                    'last_name' => $roomData['guest_last_name'] ?? '',
                    'email' => $roomData['guest_email'] ?? null,
                    'phone' => $roomData['guest_phone'],
                    'id_type' => $roomData['guest_id_type'],
                    'id_number' => $roomData['guest_id_number'],
                ]);

                // ⚡ Use pre-loaded room instead of querying in loop
                $room = $rooms->get($roomData['hotel_room_id']);

                if (!$room) {
                    throw new \Exception("Kamar dengan ID {$roomData['hotel_room_id']} tidak ditemukan.");
                }

                // Check room availability
                if ($room->status !== 'vacant_clean') {
                    throw new \Exception("Kamar {$room->room_number} tidak tersedia.");
                }

                $totalRoomCharge = $roomData['room_rate_per_night'] * $nights;
                $taxAmount = $this->calculateTax($totalRoomCharge);
                $serviceCharge = $this->calculateServiceCharge($totalRoomCharge);

                // Create room stay
                $roomStay = RoomStay::create([
                    'property_id' => $property->id,
                    'hotel_room_id' => $room->id,
                    'guest_id' => $guest->id,
                    'room_type_id' => $room->room_type_id,
                    'source' => $validated['source'],
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'actual_check_in' => now(),
                    'room_rate_per_night' => $roomData['room_rate_per_night'],
                    'bar_level' => $barLevel,
                    'total_room_charge' => $totalRoomCharge,
                    'tax_amount' => $taxAmount,
                    'service_charge' => $serviceCharge,
                    'adults' => $roomData['adults'],
                    'children' => $roomData['children'] ?? 0,
                    'special_requests' => $validated['special_requests'],
                    'status' => 'checked_in',
                    'status_changed_at' => now(),
                    'payment_status' => 'unpaid',
                    'checked_in_by' => $user->id,
                    'notes' => "Group: {$validated['group_name']}",
                ]);

                $roomStays[] = $roomStay;
            }

            // ⚡ PERFORMANCE FIX: Bulk update room statuses instead of updating one by one
            HotelRoom::whereIn('id', $roomIds)->update(['status' => 'occupied']);

            // ⚡ PERFORMANCE FIX: Update daily occupancy once after all check-ins
            $this->frontOfficeService->updateDailyOccupancy($property->id, $checkInDate);

            // Log activity
            $roomNumbers = collect($roomStays)->map(fn($rs) => $rs->hotelRoom->room_number)->implode(', ');
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'create',
                'description' => $user->name . " melakukan group check-in '{$validated['group_name']}', {$totalRooms} kamar: {$roomNumbers}, dari {$checkInDate->format('d/m/Y')} sampai {$checkOutDate->format('d/m/Y')}",
                'loggable_id' => null,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('frontoffice.index')
                ->with('success', "Group check-in berhasil! {$totalRooms} kamar telah di-check-in untuk group '{$validated['group_name']}'");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process group check-in', [
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan group check-in. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Mark reservation as no-show.
     */
    public function markNoShow(RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke reservasi ini.');
        }

        // Can only mark confirmed/reserved stays as no-show
        if (!in_array($roomStay->status, ['confirmed', 'reserved'])) {
            return redirect()->back()
                ->with('error', 'Hanya reservasi dengan status Confirmed/Reserved yang bisa di-mark sebagai No-Show.');
        }

        // Check if check-in date has passed
        if ($roomStay->check_in_date->isFuture()) {
            return redirect()->back()
                ->with('error', 'Tidak bisa mark No-Show untuk reservasi yang belum mencapai tanggal check-in.');
        }

        return view('frontoffice.no-show', compact('roomStay', 'property'));
    }

    /**
     * Process no-show with cancellation fee.
     */
    public function processNoShow(Request $request, RoomStay $roomStay)
    {
        $validated = $request->validate([
            'cancellation_fee' => 'required|numeric|min:0',
            'charge_method' => 'required|in:deposit,charge_card,waive',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Update room stay status to no_show
            $roomStay->update([
                'status' => 'no_show',
                'notes' => ($roomStay->notes ?? '') . "\n[NO-SHOW] " . ($validated['notes'] ?? 'Marked as no-show'),
            ]);

            // Handle cancellation fee
            if ($validated['cancellation_fee'] > 0) {
                if ($validated['charge_method'] === 'deposit') {
                    // Deduct from deposit if available
                    $depositAvailable = $roomStay->reservation->deposit_amount ?? 0;
                    $refundAmount = max(0, $depositAvailable - $validated['cancellation_fee']);

                    // Create payment record for cancellation fee
                    \App\Models\Payment::create([
                        'room_stay_id' => $roomStay->id,
                        'property_id' => $property->id,
                        'amount' => -$validated['cancellation_fee'], // Negative for fee
                        'payment_method' => 'deposit',
                        'payment_date' => now(),
                        'status' => 'completed',
                        'notes' => 'No-show cancellation fee',
                        'processed_by' => $user->id,
                    ]);
                } elseif ($validated['charge_method'] === 'charge_card') {
                    // Record as receivable (to be charged)
                    \App\Models\Payment::create([
                        'room_stay_id' => $roomStay->id,
                        'property_id' => $property->id,
                        'amount' => -$validated['cancellation_fee'],
                        'payment_method' => 'credit_card',
                        'payment_date' => now(),
                        'status' => 'pending',
                        'notes' => 'No-show cancellation fee - to be charged',
                        'processed_by' => $user->id,
                    ]);
                }
                // If waive, don't create payment record
            }

            // Release the room
            $roomStay->hotelRoom->update([
                'status' => 'available',
                'current_guest_id' => null,
            ]);

            // Activity log
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'no_show',
                'description' => "{$user->name} marked reservation as NO-SHOW for {$roomStay->guest->full_name}, Room {$roomStay->hotelRoom->room_number}. Cancellation fee: Rp " . number_format($validated['cancellation_fee'], 0, ',', '.'),
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('frontoffice.index')
                ->with('success', 'Reservasi berhasil di-mark sebagai No-Show.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process no-show', [
                'room_stay_id' => $roomStay->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses no-show. Silakan coba lagi.');
        }
    }

    /**
     * Request early check-in with additional charge.
     */
    public function requestEarlyCheckin(Request $request, RoomStay $roomStay)
    {
        $validated = $request->validate([
            'requested_time' => 'required|date',
            'additional_charge' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $property = $user->property;

        if ($roomStay->property_id !== $property->id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Update check-in time
            $roomStay->update([
                'check_in_date' => $validated['requested_time'],
                'notes' => ($roomStay->notes ?? '') . "\n[EARLY CHECK-IN] Requested: {$validated['requested_time']}, Charge: Rp " . number_format($validated['additional_charge'], 0, ',', '.'),
            ]);

            // Add charge if applicable
            if ($validated['additional_charge'] > 0) {
                $roomStay->update([
                    'total_room_charge' => $roomStay->total_room_charge + $validated['additional_charge'],
                ]);
            }

            // Activity log
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'update',
                'description' => "{$user->name} approved early check-in for {$roomStay->guest->full_name}, Room {$roomStay->hotelRoom->room_number}",
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Early check-in request approved.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process early check-in.');
        }
    }

    /**
     * Request late checkout with additional charge.
     */
    public function requestLateCheckout(Request $request, RoomStay $roomStay)
    {
        $validated = $request->validate([
            'requested_time' => 'required|date',
            'additional_charge' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $property = $user->property;

        if ($roomStay->property_id !== $property->id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Update checkout time
            $roomStay->update([
                'check_out_date' => $validated['requested_time'],
                'notes' => ($roomStay->notes ?? '') . "\n[LATE CHECKOUT] Requested: {$validated['requested_time']}, Charge: Rp " . number_format($validated['additional_charge'], 0, ',', '.'),
            ]);

            // Add charge if applicable
            if ($validated['additional_charge'] > 0) {
                $roomStay->update([
                    'total_room_charge' => $roomStay->total_room_charge + $validated['additional_charge'],
                ]);
            }

            // Activity log
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'update',
                'description' => "{$user->name} approved late checkout for {$roomStay->guest->full_name}, Room {$roomStay->hotelRoom->room_number}",
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Late checkout request approved.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process late checkout.');
        }
    }

    /**
     * Display booking history with all room stays.
     */
    public function bookingHistory(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Query builder for room stays
        $query = RoomStay::where('property_id', $property->id)
            ->with(['guest', 'hotelRoom', 'roomType']);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by guest name if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('guest', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by room number if provided
        if ($request->filled('room')) {
            $query->whereHas('hotelRoom', function ($q) use ($request) {
                $q->where('room_number', 'like', "%{$request->room}%");
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('check_in_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('check_out_date', '<=', $request->end_date);
        }

        // Sort by latest check-in date
        $roomStays = $query->orderBy('check_in_date', 'desc')
            ->paginate(20);

        return view('frontoffice.booking-history', compact('property', 'roomStays'));
    }
}
