<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialEntry;
use App\Models\Property;

class ClearBudgetData extends Command
{
    protected $signature = 'budget:clear {property_id} {year} {--category_id=} {--force}';
    protected $description = 'Clear budget data for a specific year (keeps actual values)';

    public function handle()
    {
        $propertyId = $this->argument('property_id');
        $year = $this->argument('year');
        $categoryId = $this->option('category_id');
        $force = $this->option('force');

        $property = Property::find($propertyId);
        if (!$property) {
            $this->error("Property ID {$propertyId} not found!");
            return 1;
        }

        $this->info("Clear Budget Data");
        $this->info("Property: {$property->name} (ID: {$propertyId})");
        $this->info("Year: {$year}");

        if ($categoryId) {
            $this->info("Category ID: {$categoryId}");
        } else {
            $this->info("All categories");
        }

        $this->info(str_repeat('=', 80));

        // Build query
        $query = FinancialEntry::where('property_id', $propertyId)
            ->where('year', $year);

        if ($categoryId) {
            $query->where('financial_category_id', $categoryId);
        }

        $entriesToClear = $query->get();
        $count = $entriesToClear->count();

        if ($count == 0) {
            $this->info("No budget data found to clear.");
            return 0;
        }

        $this->warn("Found {$count} entries to clear");

        // Show summary
        $totalBudget = $entriesToClear->sum('budget_value');
        $this->info("Total budget that will be cleared: " . number_format($totalBudget, 2));

        if (!$force) {
            if (!$this->confirm('Do you want to proceed? This will SET BUDGET_VALUE = 0 (actual values will be preserved)', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Clear budget values (set to 0, but keep actual values)
        $cleared = 0;
        foreach ($entriesToClear as $entry) {
            $oldBudget = $entry->budget_value;
            $entry->budget_value = 0;
            $entry->save();

            \Log::info("Cleared budget for entry {$entry->id}", [
                'property_id' => $propertyId,
                'category_id' => $entry->financial_category_id,
                'year' => $year,
                'month' => $entry->month,
                'old_budget' => $oldBudget,
                'actual_value_preserved' => $entry->actual_value,
            ]);

            $cleared++;
        }

        $this->info("✓ Successfully cleared budget for {$cleared} entries");
        $this->info("Actual values have been preserved.");

        return 0;
    }
}
