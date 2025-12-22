<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialEntry;
use App\Models\FinancialCategory;
use App\Models\Property;

class ShowBudgetDetails extends Command
{
    protected $signature = 'budget:show {property_id} {year} {category_id}';
    protected $description = 'Show detailed budget data for a specific category';

    public function handle()
    {
        $propertyId = $this->argument('property_id');
        $year = $this->argument('year');
        $categoryId = $this->argument('category_id');

        $property = Property::find($propertyId);
        $category = FinancialCategory::find($categoryId);

        if (!$property) {
            $this->error("Property ID {$propertyId} not found!");
            return 1;
        }

        if (!$category) {
            $this->error("Category ID {$categoryId} not found!");
            return 1;
        }

        $this->info("Budget Details:");
        $this->info("Property: {$property->name} (ID: {$propertyId})");
        $this->info("Category: {$category->name} (ID: {$categoryId})");
        $this->info("Full Path: {$category->getFullPath()}");
        $this->info("Year: {$year}");
        $this->info(str_repeat('=', 100));

        $entries = FinancialEntry::where('property_id', $propertyId)
            ->where('financial_category_id', $categoryId)
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        if ($entries->isEmpty()) {
            $this->warn("No entries found for this category!");
            return 1;
        }

        $table = [];
        $totalBudget = 0;
        $totalActual = 0;
        $totalForecast = 0;

        foreach ($entries as $entry) {
            $monthName = \Carbon\Carbon::create($year, $entry->month, 1)->format('F');

            $table[] = [
                $entry->month,
                $monthName,
                number_format($entry->budget_value, 2),
                number_format($entry->actual_value, 2),
                number_format($entry->forecast_value, 2),
                $entry->id,
            ];

            $totalBudget += $entry->budget_value;
            $totalActual += $entry->actual_value;
            $totalForecast += $entry->forecast_value;
        }

        // Add totals row
        $table[] = [
            '',
            'TOTAL',
            number_format($totalBudget, 2),
            number_format($totalActual, 2),
            number_format($totalForecast, 2),
            '',
        ];

        // Add average row
        $avgBudget = $entries->count() > 0 ? $totalBudget / $entries->count() : 0;
        $avgActual = $entries->count() > 0 ? $totalActual / $entries->count() : 0;
        $avgForecast = $entries->count() > 0 ? $totalForecast / $entries->count() : 0;

        $table[] = [
            '',
            'AVERAGE',
            number_format($avgBudget, 2),
            number_format($avgActual, 2),
            number_format($avgForecast, 2),
            '',
        ];

        $this->table(
            ['Month #', 'Month', 'Budget', 'Actual', 'Forecast', 'Entry ID'],
            $table
        );

        $this->info("\nSummary:");
        $this->info("  Total Months: " . $entries->count());
        $this->info("  Expected Total (if 12 months × average): " . number_format($avgBudget * 12, 2));

        if ($entries->count() !== 12) {
            $this->warn("  ⚠ Warning: Expected 12 months, found " . $entries->count());
        }

        return 0;
    }
}
