<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialEntry;
use App\Models\FinancialCategory;
use App\Models\Property;

class DetectAndFixBudgetIssues extends Command
{
    protected $signature = 'budget:detect-and-fix {property_id} {year} {--dry-run} {--auto-fix}';
    protected $description = 'Detect budget inconsistencies and optionally fix them automatically';

    public function handle()
    {
        $propertyId = $this->argument('property_id');
        $year = $this->argument('year');
        $dryRun = $this->option('dry-run');
        $autoFix = $this->option('auto-fix');

        $property = Property::find($propertyId);
        if (!$property) {
            $this->error("Property ID {$propertyId} not found!");
            return 1;
        }

        $this->info("Detecting Budget Issues");
        $this->info("Property: {$property->name} (ID: {$propertyId})");
        $this->info("Year: {$year}");
        $this->info(str_repeat('=', 100));

        // Get all categories with budget data
        $entries = FinancialEntry::where('property_id', $propertyId)
            ->where('year', $year)
            ->orderBy('financial_category_id')
            ->orderBy('month')
            ->get();

        $grouped = $entries->groupBy('financial_category_id');
        $issues = [];
        $fixable = [];

        foreach ($grouped as $catId => $catEntries) {
            $category = FinancialCategory::find($catId);
            if (!$category) continue;

            // Check if all 12 months exist
            if ($catEntries->count() != 12) {
                $issues[] = [
                    'category_id' => $catId,
                    'category_name' => $category->name,
                    'issue' => 'MISSING_MONTHS',
                    'details' => "Only {$catEntries->count()} months found",
                    'fixable' => false,
                ];
                continue;
            }

            // Get budget values for all months
            $budgetValues = $catEntries->pluck('budget_value', 'month')->toArray();

            // Check if January is different from other months
            $januaryValue = $budgetValues[1] ?? 0;
            $otherMonthsValues = array_slice($budgetValues, 1, 11, true); // Feb-Dec

            // Calculate mode (most common value) for Feb-Dec
            $valueCounts = array_count_values(array_map('strval', $otherMonthsValues));
            arsort($valueCounts);
            $mostCommonValue = (float) key($valueCounts);
            $mostCommonCount = current($valueCounts);

            // If 10+ months have the same value, assume that's the correct value
            if ($mostCommonCount >= 10 && $januaryValue != $mostCommonValue && $mostCommonValue > 0) {
                $yearlyTotal = $catEntries->sum('budget_value');
                $expectedTotal = $mostCommonValue * 12;
                $difference = $yearlyTotal - $expectedTotal;

                $issues[] = [
                    'category_id' => $catId,
                    'category_name' => $category->name,
                    'issue' => 'JANUARY_INCONSISTENT',
                    'january_value' => $januaryValue,
                    'expected_value' => $mostCommonValue,
                    'difference' => $januaryValue - $mostCommonValue,
                    'yearly_total' => $yearlyTotal,
                    'expected_yearly' => $expectedTotal,
                    'yearly_difference' => $difference,
                    'fixable' => true,
                ];

                $fixable[] = [
                    'category_id' => $catId,
                    'category_name' => $category->name,
                    'correct_value' => $mostCommonValue,
                    'entry_id' => $catEntries->where('month', 1)->first()->id,
                ];
            }
        }

        // Display results
        if (count($issues) == 0) {
            $this->info("\n✓ No issues found! All budget data is consistent.");
            return 0;
        }

        $this->warn("\n" . count($issues) . " Issue(s) Found:");
        $this->warn(str_repeat('=', 100));

        foreach ($issues as $issue) {
            $this->newLine();
            $this->error("❌ {$issue['issue']}: {$issue['category_name']} (ID: {$issue['category_id']})");

            if ($issue['issue'] == 'JANUARY_INCONSISTENT') {
                $this->line("  January value:    " . number_format($issue['january_value'], 2));
                $this->line("  Expected value:   " . number_format($issue['expected_value'], 2));
                $this->line("  Difference:       " . number_format($issue['difference'], 2));
                $this->line("  Yearly total:     " . number_format($issue['yearly_total'], 2));
                $this->line("  Expected yearly:  " . number_format($issue['expected_yearly'], 2));
                $this->line("  Yearly diff:      " . number_format($issue['yearly_difference'], 2));
            } else {
                $this->line("  " . $issue['details']);
            }
        }

        // Auto-fix if requested
        if (count($fixable) > 0) {
            $this->newLine();
            $this->info(count($fixable) . " fixable issue(s) detected");

            if ($dryRun) {
                $this->info("DRY RUN MODE - No changes will be made");
                return 0;
            }

            if (!$autoFix) {
                if (!$this->confirm('Do you want to fix these issues?', false)) {
                    $this->info('Operation cancelled.');
                    return 0;
                }
            }

            $this->info("\nFixing issues...");
            $fixed = 0;

            foreach ($fixable as $fix) {
                $entry = FinancialEntry::find($fix['entry_id']);
                if (!$entry) continue;

                $oldValue = $entry->budget_value;
                $entry->budget_value = $fix['correct_value'];
                $entry->save();

                $this->info("✓ Fixed {$fix['category_name']}: {$oldValue} → {$fix['correct_value']}");

                \Log::info("Auto-fixed budget inconsistency", [
                    'category_id' => $fix['category_id'],
                    'category_name' => $fix['category_name'],
                    'entry_id' => $fix['entry_id'],
                    'old_value' => $oldValue,
                    'new_value' => $fix['correct_value'],
                ]);

                $fixed++;
            }

            $this->newLine();
            $this->info("✓ Successfully fixed {$fixed} issue(s)");

            return 0;
        }

        return 1;
    }
}
