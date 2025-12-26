<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialEntry;
use App\Models\FinancialCategory;
use App\Models\Property;

class FixBudgetJanuary extends Command
{
    protected $signature = 'budget:fix-january {property_id} {year} {category_id} {correct_value}';
    protected $description = 'Fix incorrect January budget value for a specific category';

    public function handle()
    {
        $propertyId = $this->argument('property_id');
        $year = $this->argument('year');
        $categoryId = $this->argument('category_id');
        $correctValue = $this->argument('correct_value');

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

        // Get January entry
        $entry = FinancialEntry::where('property_id', $propertyId)
            ->where('financial_category_id', $categoryId)
            ->where('year', $year)
            ->where('month', 1)
            ->first();

        if (!$entry) {
            $this->error("January entry not found for this category!");
            return 1;
        }

        $oldValue = $entry->budget_value;
        $this->info("Current January budget: " . number_format($oldValue, 2));
        $this->info("New value will be: " . number_format($correctValue, 2));

        if (!$this->confirm('Do you want to proceed with this change?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $entry->budget_value = $correctValue;
        $entry->save();

        $this->info("✓ Successfully updated January budget from " . number_format($oldValue, 2) . " to " . number_format($correctValue, 2));

        // Show new total
        $totalBudget = FinancialEntry::where('property_id', $propertyId)
            ->where('financial_category_id', $categoryId)
            ->where('year', $year)
            ->sum('budget_value');

        $this->info("New yearly total: " . number_format($totalBudget, 2));

        return 0;
    }
}
