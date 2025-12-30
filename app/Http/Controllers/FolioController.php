<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RoomStay;
use App\Models\Property;
use App\Enums\RoomStayStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FolioController extends Controller
{
    /**
     * Show folio detail for a room stay.
     */
    public function show(RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke folio ini.');
        }

        // Eager load all relationships to prevent N+1 queries
        $roomStay->load([
            'guest',
            'hotelRoom.roomType',
            'property',
            'fnbOrders' => function ($query) {
                $query->with('items.menuItem');
            },
            'payments',
            'checkedInBy',
            'checkedOutBy',
        ]);

        // Calculate folio summary using aggregates to avoid loading all records
        $roomCharges = $roomStay->total_room_charge;
        $breakfastCharges = $roomStay->total_breakfast_charge ?? 0;

        // Use aggregate queries instead of loading all records
        $fnbAggregates = DB::table('fnb_orders')
            ->where('room_stay_id', $roomStay->id)
            ->selectRaw('
                COALESCE(SUM(subtotal), 0) as fnb_subtotal,
                COALESCE(SUM(tax_amount), 0) as fnb_tax,
                COALESCE(SUM(service_charge), 0) as fnb_service,
                COALESCE(SUM(total_amount), 0) as fnb_total
            ')
            ->first();

        $fnbSubtotal = $fnbAggregates->fnb_subtotal ?? 0;
        $fnbTax = $fnbAggregates->fnb_tax ?? 0;
        $fnbService = $fnbAggregates->fnb_service ?? 0;
        $fnbCharges = $fnbAggregates->fnb_total ?? 0;

        $subtotal = $roomCharges + $breakfastCharges + $fnbSubtotal;
        $taxAmount = $roomStay->tax_amount + $fnbTax;
        $serviceCharge = $roomStay->service_charge + $fnbService;
        $totalCharges = $subtotal + $taxAmount + $serviceCharge - ($roomStay->discount_amount ?? 0);
        $totalPayments = $roomStay->payments->sum('amount');
        $balance = $totalCharges - $totalPayments;

        // Get room changes history
        $roomChanges = \App\Models\RoomChange::where('room_stay_id', $roomStay->id)
            ->with(['oldRoom', 'newRoom', 'processedBy'])
            ->orderBy('processed_at')
            ->get();

        // Get daily breakdown
        $dailyCharges = $this->calculateDailyCharges($roomStay);

        return view('frontoffice.folio', compact(
            'roomStay',
            'roomCharges',
            'breakfastCharges',
            'fnbCharges',
            'subtotal',
            'taxAmount',
            'serviceCharge',
            'totalCharges',
            'totalPayments',
            'balance',
            'roomChanges',
            'dailyCharges'
        ));
    }

    /**
     * Print folio.
     */
    public function print(RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke folio ini.');
        }

        // Eager load all relationships to prevent N+1 queries
        $roomStay->load([
            'guest',
            'hotelRoom.roomType',
            'property',
            'fnbOrders' => function ($query) {
                $query->with('items.menuItem');
            },
            'payments',
            'checkedInBy',
            'checkedOutBy',
        ]);

        // Calculate folio summary using aggregates
        $roomCharges = $roomStay->total_room_charge;
        $breakfastCharges = $roomStay->total_breakfast_charge ?? 0;

        // Use aggregate queries instead of loading all records
        $fnbAggregates = DB::table('fnb_orders')
            ->where('room_stay_id', $roomStay->id)
            ->selectRaw('
                COALESCE(SUM(subtotal), 0) as fnb_subtotal,
                COALESCE(SUM(tax_amount), 0) as fnb_tax,
                COALESCE(SUM(service_charge), 0) as fnb_service,
                COALESCE(SUM(total_amount), 0) as fnb_total
            ')
            ->first();

        $fnbSubtotal = $fnbAggregates->fnb_subtotal ?? 0;
        $fnbTax = $fnbAggregates->fnb_tax ?? 0;
        $fnbService = $fnbAggregates->fnb_service ?? 0;
        $fnbCharges = $fnbAggregates->fnb_total ?? 0;

        $subtotal = $roomCharges + $breakfastCharges + $fnbSubtotal;
        $taxAmount = $roomStay->tax_amount + $fnbTax;
        $serviceCharge = $roomStay->service_charge + $fnbService;
        $totalCharges = $subtotal + $taxAmount + $serviceCharge - ($roomStay->discount_amount ?? 0);
        $totalPayments = $roomStay->payments->sum('amount');
        $balance = $totalCharges - $totalPayments;

        // Get room changes history
        $roomChanges = \App\Models\RoomChange::where('room_stay_id', $roomStay->id)
            ->with(['oldRoom', 'newRoom', 'processedBy'])
            ->orderBy('processed_at')
            ->get();

        // Get daily breakdown
        $dailyCharges = $this->calculateDailyCharges($roomStay);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $roomStay->property_id,
            'action' => 'export',
            'description' => auth()->user()->name . " mencetak folio untuk tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}, konfirmasi: {$roomStay->confirmation_number}",
            'loggable_id' => $roomStay->id,
            'loggable_type' => RoomStay::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('frontoffice.folio-print', compact(
            'roomStay',
            'roomCharges',
            'breakfastCharges',
            'fnbCharges',
            'subtotal',
            'taxAmount',
            'serviceCharge',
            'totalCharges',
            'totalPayments',
            'balance',
            'roomChanges',
            'dailyCharges'
        ));
    }

    /**
     * Calculate daily room charges breakdown.
     */
    private function calculateDailyCharges(RoomStay $roomStay)
    {
        $charges = [];

        if (!$roomStay->actual_check_in) {
            return $charges;
        }

        $checkIn = $roomStay->actual_check_in->startOfDay();
        $checkOut = $roomStay->actual_check_out
            ? $roomStay->actual_check_out->startOfDay()
            : $roomStay->check_out_date->startOfDay();

        $currentDate = $checkIn->copy();
        $nightNumber = 1;

        while ($currentDate->lt($checkOut)) {
            $charges[] = [
                'date' => $currentDate->copy(),
                'night' => $nightNumber,
                'rate' => $roomStay->room_rate_per_night,
                'description' => "Room {$roomStay->hotelRoom->room_number} - Night {$nightNumber}",
            ];

            $currentDate->addDay();
            $nightNumber++;
        }

        return $charges;
    }

    /**
     * Add manual charge to folio.
     */
    public function addCharge(Request $request, RoomStay $roomStay)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'charge_type' => 'required|in:laundry,minibar,telephone,parking,other',
        ]);

        // This would typically create a separate charges table
        // For now, we'll add to notes
        $chargeInfo = sprintf(
            "%s: %s - Rp %s",
            ucfirst($validated['charge_type']),
            $validated['description'],
            number_format($validated['amount'], 0, ',', '.')
        );

        $roomStay->update([
            'notes' => $roomStay->notes . "\n[CHARGE] " . $chargeInfo,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $roomStay->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " menambahkan charge manual ke folio tamu {$roomStay->guest->full_name}, {$chargeInfo}",
            'loggable_id' => $roomStay->id,
            'loggable_type' => RoomStay::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', 'Charge berhasil ditambahkan ke folio');
    }

    /**
     * 💰 Add payment to folio.
     */
    public function addPayment(Request $request, RoomStay $roomStay)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke folio ini.');
        }

        // Validate request data
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,other',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Record payment using RoomStay method
            $payment = $roomStay->recordPayment(
                amount: $validated['amount'],
                method: $validated['payment_method'],
                notes: $validated['notes'] ?? null,
                user: $user
            );

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $roomStay->property_id,
                'action' => 'create',
                'description' => $user->name . " menambahkan pembayaran Rp " . number_format($validated['amount'], 0, ',', '.')
                    . " ({$validated['payment_method']}) untuk tamu {$roomStay->guest->full_name}, kamar {$roomStay->hotelRoom->room_number}",
                'loggable_id' => $payment->id,
                'loggable_type' => \App\Models\Payment::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pembayaran berhasil ditambahkan! Total dibayar: Rp ' . number_format($roomStay->fresh()->paid_amount, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal menambahkan pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✏️ Update payment in folio.
     */
    public function updatePayment(Request $request, RoomStay $roomStay, \App\Models\Payment $payment)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke folio ini.');
        }

        // Validate payment belongs to this room stay
        if ($payment->payable_id !== $roomStay->id || $payment->payable_type !== RoomStay::class) {
            abort(403, 'Pembayaran ini tidak terkait dengan folio ini.');
        }

        // Validate request data
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,other',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Calculate the difference in payment amount
            $oldAmount = $payment->amount;
            $newAmount = $validated['amount'];
            $difference = $newAmount - $oldAmount;

            // Update payment record
            $payment->update([
                'amount' => $newAmount,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update room stay's paid_amount
            $roomStay->increment('paid_amount', $difference);
            $roomStay->updatePaymentStatus();

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $roomStay->property_id,
                'action' => 'update',
                'description' => $user->name . " mengubah pembayaran dari Rp " . number_format($oldAmount, 0, ',', '.')
                    . " menjadi Rp " . number_format($newAmount, 0, ',', '.')
                    . " ({$validated['payment_method']}) untuk tamu {$roomStay->guest->full_name}",
                'loggable_id' => $payment->id,
                'loggable_type' => \App\Models\Payment::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pembayaran berhasil diupdate! Total dibayar: Rp ' . number_format($roomStay->fresh()->paid_amount, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal mengupdate pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * 🗑️ Delete payment from folio.
     */
    public function deletePayment(Request $request, RoomStay $roomStay, \App\Models\Payment $payment)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($roomStay->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke folio ini.');
        }

        // Validate payment belongs to this room stay
        if ($payment->payable_id !== $roomStay->id || $payment->payable_type !== RoomStay::class) {
            abort(403, 'Pembayaran ini tidak terkait dengan folio ini.');
        }

        try {
            DB::beginTransaction();

            $paymentAmount = $payment->amount;
            $paymentMethod = $payment->payment_method;

            // Reduce room stay's paid_amount
            $roomStay->decrement('paid_amount', $paymentAmount);
            $roomStay->updatePaymentStatus();

            // Log activity before deleting
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $roomStay->property_id,
                'action' => 'delete',
                'description' => $user->name . " menghapus pembayaran Rp " . number_format($paymentAmount, 0, ',', '.')
                    . " ({$paymentMethod}) untuk tamu {$roomStay->guest->full_name}",
                'loggable_id' => $payment->id,
                'loggable_type' => \App\Models\Payment::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Delete payment record
            $payment->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Pembayaran berhasil dihapus! Total dibayar: Rp ' . number_format($roomStay->fresh()->paid_amount, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }
}
