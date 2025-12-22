<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RoomStay;
use App\Models\Property;
use Illuminate\Http\Request;

class FolioController extends Controller
{
    /**
     * Show folio detail for a room stay.
     */
    public function show(RoomStay $roomStay)
    {
        $roomStay->load([
            'guest',
            'hotelRoom.roomType',
            'property',
            'fnbOrders.items.menuItem',
            'payments',
            'checkedInBy',
            'checkedOutBy',
        ]);

        // Calculate folio summary
        $roomCharges = $roomStay->total_room_charge;
        $fnbCharges = $roomStay->fnbOrders->sum('total_amount');
        $subtotal = $roomCharges + $fnbCharges;
        $taxAmount = $roomStay->tax_amount + $roomStay->fnbOrders->sum('tax_amount');
        $serviceCharge = $roomStay->service_charge + $roomStay->fnbOrders->sum('service_charge');
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
        $roomStay->load([
            'guest',
            'hotelRoom.roomType',
            'property',
            'fnbOrders.items.menuItem',
            'payments',
            'checkedInBy',
            'checkedOutBy',
        ]);

        // Calculate folio summary
        $roomCharges = $roomStay->total_room_charge;
        $fnbCharges = $roomStay->fnbOrders->sum('total_amount');
        $subtotal = $roomCharges + $fnbCharges;
        $taxAmount = $roomStay->tax_amount + $roomStay->fnbOrders->sum('tax_amount');
        $serviceCharge = $roomStay->service_charge + $roomStay->fnbOrders->sum('service_charge');
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
}
