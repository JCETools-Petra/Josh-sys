<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialEntry;
use App\Models\FinancialCategory;
use App\Models\Property;

class VerifyBudgetData extends Command
{
    protected $signature = 'budget:verify {property_id} {year} {--category_id=} {--fix}';
    protected $description = 'Verify budget data integrity and optionally fix issues';

    public function handle()
    {
        $propertyId = $this->argument('property_id');
        $year = $this->argument('year');
        $categoryId = $this->option('category_id');
        $fix = $this->option('fix');

        $property = Property::find($propertyId);
        if (!$property) {
            $this->error("Property ID {$propertyId} not found!");
            return 1;
        }

        $this->info("Verifying budget data for {$property->name} - Year {$year}");
        $this->info(str_repeat('=', 80));

        $query = FinancialEntry::where('property_id', $propertyId)
            ->where('year', $year);

        if ($categoryId) {
            $query->where('financial_category_id', $categoryId);
        }

        $entries = $query->orderBy('financial_category_id')
            ->orderBy('month')
            ->get();

        $grouped = $entries->groupBy('financial_category_id');

        $issues = [];
        $stats = [];

        foreach ($grouped as $catId => $catEntries) {
            $category = FinancialCategory::find($catId);
            if (!$category) {
                $this->warn("Category ID {$catId} not found in database!");
                continue;
            }

            $categoryName = $category->name;
            $categoryPath = $category->getFullPath();

            $monthCount = $catEntries->count();
            $budgetSum = $catEntries->sum('budget_value');
            $actualSum = $catEntries->sum('actual_value');
            $forecastSum = $catEntries->sum('forecast_value');

            $avgBudget = $monthCount > 0 ? $budgetSum / $monthCount : 0;

            $stats[] = [
                'category_id' => $catId,
                'category_name' => $categoryName,
                'category_path' => $categoryPath,
                'month_count' => $monthCount,
                'budget_yearly' => $budgetSum,
                'budget_monthly_avg' => $avgBudget,
                'actual_yearly' => $actualSum,
                'forecast_yearly' => $forecastSum,
            ];

            // Check for issues
            if ($monthCount != 12) {
                $issues[] = [
                    'type' => 'MISSING_MONTHS',
                    'category_id' => $catId,
                    'category_name' => $categoryName,
                    'message' => "Expected 12 months, found {$monthCount}",
                    'months_present' => $catEntries->pluck('month')->toArray(),
                ];
            }

            // Check for duplicate months
            $months = $catEntries->pluck('month')->toArray();
            $duplicates = array_diff_assoc($months, array_unique($months));
            if (count($duplicates) > 0) {
                $issues[] = [
                    'type' => 'DUPLICATE_MONTHS',
                    'category_id' => $catId,
                    'category_name' => $categoryName,
                    'message' => "Duplicate months found",
                    'duplicate_months' => $duplicates,
                ];
            }

            // Check for invalid month values
            foreach ($catEntries as $entry) {
                if ($entry->month < 1 || $entry->month > 12) {
                    $issues[] = [
                        'type' => 'INVALID_MONTH',
                        'category_id' => $catId,
                        'category_name' => $categoryName,
                        'message' => "Invalid month value: {$entry->month}",
                        'entry_id' => $entry->id,
                    ];
                }
            }
        }

        // Display statistics
        $this->info("\nBudget Statistics:");
        $this->info(str_repeat('-', 80));

        $table = [];
        foreach ($stats as $stat) {
            $table[] = [
                $stat['category_id'],
                substr($stat['category_name'], 0, 30),
                number_format($stat['budget_monthly_avg'], 2),
                number_format($stat['budget_yearly'], 2),
                $stat['month_count'],
            ];
        }

        $this->table(
            ['ID', 'Category', 'Avg Monthly', 'Yearly Total', 'Months'],
            $table
        );

        // Display issues
        if (count($issues) > 0) {
            $this->error("\n" . count($issues) . " Issue(s) Found:");
            $this->error(str_repeat('-', 80));

            foreach ($issues as $issue) {
                $this->warn("\n{$issue['type']}: {$issue['category_name']} (ID: {$issue['category_id']})");
                $this->line("  " . $issue['message']);

                if (isset($issue['months_present'])) {
                    $this->line("  Months present: " . implode(', ', $issue['months_present']));
                }
                if (isset($issue['duplicate_months'])) {
                    $this->line("  Duplicate months: " . implode(', ', $issue['duplicate_months']));
                }
            }

            if ($fix) {
                $this->warn("\n--fix option is not yet implemented");
                $this->info("Manual intervention required to fix data integrity issues");
            }

            return 1;
        } else {
            $this->info("\n✓ No issues found! Budget data is consistent.");
            return 0;
        }
    }
}
