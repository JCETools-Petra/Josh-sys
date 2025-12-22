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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FrontOfficeController extends Controller
{
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
        $checkingInToday = RoomStay::where('property_id', $property->id)
            ->checkingInToday()
            ->with(['guest', 'hotelRoom'])
            ->get();

        $checkingOutToday = RoomStay::where('property_id', $property->id)
            ->checkingOutToday()
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
            'checkingInToday',
            'checkingOutToday',
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

        $validated = $request->validate([
            'hotel_room_id' => 'required|exists:hotel_rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_rate_per_night' => 'required|numeric|min:0',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'source' => 'required|in:walk_in,ota,ta,corporate,government,compliment,house_use,affiliate,online',
            'ota_name' => 'nullable|string',
            'ota_booking_id' => 'nullable|string',
            'special_requests' => 'nullable|string',
            // Guest information
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'nullable|string|max:255',
            'guest.email' => 'nullable|email',
            'guest.phone' => 'required|string|max:20',
            'guest.id_type' => 'required|in:ktp,passport,sim,other',
            'guest.id_number' => 'required|string|max:50',
            'guest.address' => 'nullable|string',
            'guest.city' => 'nullable|string',
        ]);

        try {
            // Calculate charges
            $room = HotelRoom::findOrFail($validated['hotel_room_id']);
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);

            $totalRoomCharge = $validated['room_rate_per_night'] * $nights;
            $taxAmount = $totalRoomCharge * 0.10; // 10% tax
            $serviceCharge = $totalRoomCharge * 0.05; // 5% service charge

            // Get current BAR level from property
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

            $data = array_merge($validated, [
                'property_id' => $property->id,
                'total_room_charge' => $totalRoomCharge,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'bar_level' => $barLevel,
                'status' => 'reserved', // Mark as reservation, not checked in yet
            ]);

            $roomStay = $this->frontOfficeService->createReservation($data);

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

            return redirect()->route('frontoffice.index')
                ->with('success', "Reservasi berhasil dibuat! Confirmation Number: {$roomStay->confirmation_number}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat reservasi: ' . $e->getMessage());
        }
    }

    /**
     * Process check-in.
     */
    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $validated = $request->validate([
            'hotel_room_id' => 'required|exists:hotel_rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_rate_per_night' => 'required|numeric|min:0',
            'with_breakfast' => 'required|boolean',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'source' => 'required|in:walk_in,ota,ta,corporate,government,compliment,house_use,affiliate,online',
            'ota_name' => 'nullable|string',
            'ota_booking_id' => 'nullable|string',
            'special_requests' => 'nullable|string',
            // Guest information
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'nullable|string|max:255',
            'guest.email' => 'nullable|email',
            'guest.phone' => 'required|string|max:20',
            'guest.id_type' => 'required|in:ktp,passport,sim,other',
            'guest.id_number' => 'required|string|max:50',
            'guest.address' => 'nullable|string',
            'guest.city' => 'nullable|string',
        ]);

        try {
            // Calculate charges
            $room = HotelRoom::findOrFail($validated['hotel_room_id']);
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);

            $totalRoomCharge = $validated['room_rate_per_night'] * $nights;
            $taxAmount = $totalRoomCharge * 0.10; // 10% tax
            $serviceCharge = $totalRoomCharge * 0.05; // 5% service charge

            // Get current BAR level from property
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

            $data = array_merge($validated, [
                'property_id' => $property->id,
                'total_room_charge' => $totalRoomCharge,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'bar_level' => $barLevel,
            ]);

            $roomStay = $this->frontOfficeService->checkIn($data);

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

            return redirect()->route('frontoffice.index')
                ->with('success', "Check-in berhasil! Confirmation Number: {$roomStay->confirmation_number}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan check-in: ' . $e->getMessage());
        }
    }

    /**
     * Show check-out payment page.
     */
    public function checkOut(RoomStay $roomStay)
    {
        $roomStay->load(['guest', 'hotelRoom.roomType', 'fnbOrders.items.menuItem', 'payments']);

        return view('frontoffice.checkout-payment', compact('roomStay'));
    }

    /**
     * Process check-out with payment.
     */
    public function processCheckout(Request $request, RoomStay $roomStay)
    {
        $validated = $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,other',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.card_number_last4' => 'nullable|string|max:4',
            'payments.*.card_holder_name' => 'nullable|string|max:255',
            'payments.*.card_type' => 'nullable|string|max:50',
            'payments.*.bank_name' => 'nullable|string|max:255',
            'payments.*.reference_number' => 'nullable|string|max:255',
            'payments.*.notes' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            $property = $user->property;

            // Calculate total bill
            $totalBill = $roomStay->total_room_charge
                       + $roomStay->fnbOrders->sum('total_amount')
                       + $roomStay->tax_amount
                       + $roomStay->service_charge;

            // Calculate total payment amount
            $totalPaid = collect($validated['payments'])->sum('amount');

            // Validate total paid matches total bill
            if (abs($totalPaid - $totalBill) > 0.01) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Total pembayaran tidak sesuai dengan tagihan. Tagihan: Rp ' . number_format($totalBill, 0, ',', '.') . ', Dibayar: Rp ' . number_format($totalPaid, 0, ',', '.'));
            }

            // Create payment records
            foreach ($validated['payments'] as $paymentData) {
                \App\Models\Payment::create([
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
                    'status' => 'completed',
                    'payment_date' => now(),
                    'processed_by' => $user->id,
                ]);
            }

            // Update room stay payment status
            $roomStay->update([
                'payment_status' => 'paid',
                'paid_amount' => $totalPaid,
            ]);

            // Process checkout
            $this->frontOfficeService->checkOut($roomStay);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
                'action' => 'update',
                'description' => auth()->user()->name . " melakukan check-out tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, total tagihan: Rp " . number_format($totalBill, 0, ',', '.') . ", pembayaran: " . collect($validated['payments'])->pluck('payment_method')->implode(', ') . ", konfirmasi: {$roomStay->confirmation_number}",
                'loggable_id' => $roomStay->id,
                'loggable_type' => RoomStay::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('frontoffice.invoice', $roomStay)
                ->with('success', "Check-out berhasil untuk kamar {$roomStay->hotelRoom->room_number}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan check-out: ' . $e->getMessage());
        }
    }

    /**
     * Print invoice for a room stay.
     */
    public function printInvoice(RoomStay $roomStay)
    {
        $roomStay->load(['guest', 'hotelRoom.roomType', 'property', 'fnbOrders.items.menuItem', 'payments']);

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
        $roomStay->load(['guest', 'hotelRoom.roomType.pricingRule', 'property']);

        // Ensure room stay is active
        if ($roomStay->status !== 'checked_in') {
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
            $additionalTax = $additionalCharge * 0.10;
            $additionalService = $additionalCharge * 0.05;

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
        $roomStay->load(['guest', 'hotelRoom.roomType', 'property']);

        // Ensure room stay is active
        if ($roomStay->status !== 'checked_in') {
            return redirect()->back()
                ->with('error', 'Hanya tamu yang sedang menginap yang bisa pindah kamar.');
        }

        // Get available rooms
        $property = $roomStay->property;
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

            // Calculate remaining nights
            $remainingNights = now()->diffInDays($roomStay->check_out_date);
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
                'tax_amount' => $newTotalCharge * 0.10,
                'service_charge' => $newTotalCharge * 0.05,
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
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'source' => 'required|in:walk_in,ota,ta,corporate,government,compliment,house_use,affiliate,online',
            'special_requests' => 'nullable|string',
            'rooms' => 'required|array|min:1',
            'rooms.*.hotel_room_id' => 'required|exists:hotel_rooms,id',
            'rooms.*.room_rate_per_night' => 'required|numeric|min:0',
            'rooms.*.adults' => 'required|integer|min:1',
            'rooms.*.children' => 'nullable|integer|min:0',
            'rooms.*.guest_first_name' => 'required|string|max:255',
            'rooms.*.guest_last_name' => 'nullable|string|max:255',
            'rooms.*.guest_email' => 'nullable|email',
            'rooms.*.guest_phone' => 'required|string|max:20',
            'rooms.*.guest_id_type' => 'required|in:ktp,passport,sim,other',
            'rooms.*.guest_id_number' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $property = $user->property;
            $checkInDate = Carbon::parse($validated['check_in_date']);
            $checkOutDate = Carbon::parse($validated['check_out_date']);
            $nights = $checkInDate->diffInDays($checkOutDate);
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

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

                $room = HotelRoom::findOrFail($roomData['hotel_room_id']);

                // Check room availability
                if ($room->status !== 'vacant_clean') {
                    throw new \Exception("Kamar {$room->room_number} tidak tersedia.");
                }

                $totalRoomCharge = $roomData['room_rate_per_night'] * $nights;
                $taxAmount = $totalRoomCharge * 0.10;
                $serviceCharge = $totalRoomCharge * 0.05;

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

                // Update room status
                $room->update(['status' => 'occupied']);

                // Update daily occupancy
                $this->frontOfficeService->updateDailyOccupancy($property->id, $checkInDate);

                $roomStays[] = $roomStay;
            }

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
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan group check-in: ' . $e->getMessage());
        }
    }
}
