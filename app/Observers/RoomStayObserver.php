<?php

namespace App\Observers;

use App\Models\RoomStay;
use App\Models\DailyIncome;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RoomStayObserver
{
    /**
     * Handle the RoomStay "updated" event.
     * Auto-update daily income when room stay status changes to checked_out.
     */
    public function updated(RoomStay $roomStay): void
    {
        // Only process when status changes to checked_out
        if ($roomStay->isDirty('status') && $roomStay->status === 'checked_out') {
            $this->updateDailyIncome($roomStay);
        }
    }

    /**
     * Update daily income based on room stay checkout.
     */
    protected function updateDailyIncome(RoomStay $roomStay): void
    {
        // Calculate daily rate
        $nights = $roomStay->nights;
        if ($nights <= 0) {
            return;
        }

        $dailyRate = $roomStay->total_room_charge / $nights;

        // Determine which income column to update based on source
        $roomsColumn = $this->getRoomsColumnBySource($roomStay->source);
        $incomeColumn = $this->getIncomeColumnBySource($roomStay->source);

        // Update daily income for each night
        $period = CarbonPeriod::create(
            Carbon::parse($roomStay->check_in_date)->startOfDay(),
            Carbon::parse($roomStay->check_out_date)->subDay()->endOfDay() // Exclude checkout date
        );

        foreach ($period as $date) {
            $dailyIncome = DailyIncome::firstOrCreate(
                [
                    'property_id' => $roomStay->property_id,
                    'date' => $date->toDateString(),
                ],
                [
                    'user_id' => $roomStay->checked_in_by ?? auth()->id(),
                    'offline_rooms' => 0,
                    'offline_room_income' => 0,
                    'online_rooms' => 0,
                    'online_room_income' => 0,
                    'ta_rooms' => 0,
                    'ta_income' => 0,
                    'gov_rooms' => 0,
                    'gov_income' => 0,
                    'corp_rooms' => 0,
                    'corp_income' => 0,
                    'compliment_rooms' => 0,
                    'compliment_income' => 0,
                    'house_use_rooms' => 0,
                    'house_use_income' => 0,
                    'afiliasi_rooms' => 0,
                    'afiliasi_room_income' => 0,
                    'breakfast_income' => 0,
                    'lunch_income' => 0,
                    'dinner_income' => 0,
                    'others_income' => 0,
                ]
            );

            // Increment rooms and income
            if ($roomsColumn && $incomeColumn) {
                $dailyIncome->increment($roomsColumn, 1);
                $dailyIncome->increment($incomeColumn, $dailyRate);
            }

            // Recalculate totals
            $dailyIncome->recalculateTotals();
            $dailyIncome->save();
        }
    }

    /**
     * Get rooms column name based on source.
     */
    protected function getRoomsColumnBySource(string $source): ?string
    {
        return match($source) {
            'walk_in' => 'offline_rooms',
            'ota', 'online' => 'online_rooms',
            'ta' => 'ta_rooms',
            'government' => 'gov_rooms',
            'corporate' => 'corp_rooms',
            'compliment' => 'compliment_rooms',
            'house_use' => 'house_use_rooms',
            'affiliate' => 'afiliasi_rooms',
            default => null,
        };
    }

    /**
     * Get income column name based on source.
     */
    protected function getIncomeColumnBySource(string $source): ?string
    {
        return match($source) {
            'walk_in' => 'offline_room_income',
            'ota', 'online' => 'online_room_income',
            'ta' => 'ta_income',
            'government' => 'gov_income',
            'corporate' => 'corp_income',
            'compliment' => 'compliment_income',
            'house_use' => 'house_use_income',
            'affiliate' => 'afiliasi_room_income',
            default => null,
        };
    }
}
