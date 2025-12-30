<?php

namespace App\Observers;

use App\Models\FnbOrder;
use App\Models\DailyIncome;
use Carbon\Carbon;

class FnbOrderObserver
{
    /**
     * Handle the FnbOrder "updated" event.
     * Auto-update daily income when F&B order status changes to completed.
     */
    public function updated(FnbOrder $fnbOrder): void
    {
        // Only process when status changes to completed
        if ($fnbOrder->isDirty('status') && $fnbOrder->status === 'completed') {
            $this->updateDailyIncome($fnbOrder);
        }
    }

    /**
     * Update daily income based on F&B order completion.
     */
    protected function updateDailyIncome(FnbOrder $fnbOrder): void
    {
        $orderDate = Carbon::parse($fnbOrder->order_time)->toDateString();

        // Get or create daily income record
        $dailyIncome = DailyIncome::firstOrCreate(
            [
                'property_id' => $fnbOrder->property_id,
                'date' => $orderDate,
            ],
            [
                'user_id' => $fnbOrder->taken_by ?? auth()->id(),
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

        // Determine which F&B column to update based on order time
        $orderHour = Carbon::parse($fnbOrder->order_time)->hour;
        $incomeColumn = $this->getFnbColumnByTime($orderHour);

        // Add F&B income
        if ($incomeColumn) {
            $dailyIncome->increment($incomeColumn, $fnbOrder->total_amount);
        }

        // Recalculate totals
        $dailyIncome->recalculateTotals();
        $dailyIncome->save();
    }

    /**
     * Get F&B income column based on order time.
     */
    protected function getFnbColumnByTime(int $hour): ?string
    {
        // Breakfast: 6:00 - 10:59
        if ($hour >= 6 && $hour < 11) {
            return 'breakfast_income';
        }

        // Lunch: 11:00 - 15:59
        if ($hour >= 11 && $hour < 16) {
            return 'lunch_income';
        }

        // Dinner: 16:00 - 05:59
        if ($hour >= 16 || $hour < 6) {
            return 'dinner_income';
        }

        return 'others_income';
    }
}
