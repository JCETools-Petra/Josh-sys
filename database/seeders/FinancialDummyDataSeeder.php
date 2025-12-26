<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use App\Models\FinancialEntry;
use App\Models\Property;
use Illuminate\Database\Seeder;

class FinancialDummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates realistic dummy data for financial budgeting with
     * seasonal variations (high season / low season) typical for hotels.
     */
    public function run(): void
    {
        $properties = Property::all();

        foreach ($properties as $property) {
            $this->seedFinancialData($property);
        }

        $this->command->info('Financial dummy data seeded successfully with seasonal variations!');
    }

    /**
     * Seed financial data for a specific property.
     */
    private function seedFinancialData(Property $property): void
    {
        $this->command->info("Seeding financial data for: {$property->name}");

        // Get all leaf categories (yang bisa di-input manual)
        $categories = FinancialCategory::where('property_id', $property->id)
            ->whereDoesntHave('children')
            ->whereNull('code') // Exclude auto-calculated categories
            ->get();

        if ($categories->isEmpty()) {
            $this->command->warn("No categories found for {$property->name}. Run FinancialCategorySeeder first.");
            return;
        }

        // Seed budget for 2024 and 2025
        $this->seedBudgetData($property, $categories, 2024);
        $this->seedBudgetData($property, $categories, 2025);

        // Seed actual data for 2024 (12 months with seasonal variations)
        $this->seedActualData($property, $categories, 2024);
    }

    /**
     * Seed budget data for a year.
     */
    private function seedBudgetData(Property $property, $categories, int $year): void
    {
        foreach ($categories as $category) {
            $annualBudget = $this->getAnnualBudget($category);
            $monthlyBudget = $annualBudget / 12;

            // Distribute budget equally across 12 months
            for ($month = 1; $month <= 12; $month++) {
                FinancialEntry::updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'financial_category_id' => $category->id,
                        'year' => $year,
                        'month' => $month,
                    ],
                    [
                        'budget_value' => round($monthlyBudget, 2),
                        'actual_value' => 0, // Will be filled by actual seeder
                    ]
                );
            }
        }
    }

    /**
     * Seed actual data with seasonal variations.
     */
    private function seedActualData(Property $property, $categories, int $year): void
    {
        for ($month = 1; $month <= 12; $month++) {
            $seasonalMultiplier = $this->getSeasonalMultiplier($month);

            foreach ($categories as $category) {
                $annualBudget = $this->getAnnualBudget($category);
                $baseBudget = $annualBudget / 12;

                // Apply seasonal variation to actual value
                // Add random variance of ±10% for realism
                $variance = rand(-10, 10) / 100; // -10% to +10%
                $actualValue = $baseBudget * $seasonalMultiplier * (1 + $variance);

                FinancialEntry::where([
                    'property_id' => $property->id,
                    'financial_category_id' => $category->id,
                    'year' => $year,
                    'month' => $month,
                ])->update([
                    'actual_value' => round($actualValue, 2),
                ]);
            }
        }
    }

    /**
     * Get seasonal multiplier based on month.
     * Reflects typical hotel occupancy patterns in Indonesia.
     */
    private function getSeasonalMultiplier(int $month): float
    {
        return match($month) {
            // HIGH SEASON
            12 => 1.45, // December (Christmas & New Year) - Peak
            1 => 1.35,  // January (New Year holiday)
            6 => 1.25,  // June (School holiday starts)
            7 => 1.30,  // July (Peak school holiday)

            // SHOULDER SEASON
            5 => 0.95,  // May (Before holiday)
            8 => 1.05,  // August (End of holiday)
            11 => 0.85, // November (Before peak)

            // LOW SEASON
            2 => 0.65,  // February (Post-holiday slump)
            3 => 0.70,  // March (Low)
            4 => 0.85,  // April (Recovering)
            9 => 0.75,  // September (Low)
            10 => 0.80, // October (Recovering)

            default => 1.00,
        };
    }

    /**
     * Get realistic annual budget based on category type.
     */
    private function getAnnualBudget(FinancialCategory $category): float
    {
        $categoryName = strtolower($category->name);
        $isPayroll = $category->is_payroll;

        // Get parent category name for context
        $parent = $category->parent;
        $grandParent = $parent?->parent;
        $department = $grandParent?->name ?? $parent?->name ?? '';

        // PAYROLL CATEGORIES (Higher amounts)
        if ($isPayroll || str_contains($categoryName, 'salaries') || str_contains($categoryName, 'wages')) {
            return match(true) {
                str_contains($department, 'Front Office') => rand(180000000, 240000000), // 15-20 juta/bulan
                str_contains($department, 'Housekeeping') => rand(240000000, 360000000), // 20-30 juta/bulan (lebih banyak staff)
                str_contains($department, 'F&B Product') => rand(150000000, 210000000), // 12.5-17.5 juta/bulan
                str_contains($department, 'F&B Service') => rand(180000000, 240000000), // 15-20 juta/bulan
                str_contains($department, 'POMAC') => rand(120000000, 180000000), // 10-15 juta/bulan
                str_contains($department, 'Accounting') => rand(96000000, 144000000), // 8-12 juta/bulan
                default => rand(100000000, 200000000),
            };
        }

        // SERVICE CHARGE
        if (str_contains($categoryName, 'service charge')) {
            return rand(60000000, 120000000); // 5-10 juta/bulan
        }

        // EMPLOYEE BENEFITS / BPJS
        if (str_contains($categoryName, 'benefits') || str_contains($categoryName, 'bpjs')) {
            return rand(24000000, 48000000); // 2-4 juta/bulan
        }

        // COST OF GOODS SOLD
        if (str_contains($categoryName, 'food cost')) {
            return rand(180000000, 300000000); // 15-25 juta/bulan
        }
        if (str_contains($categoryName, 'beverage cost')) {
            return rand(72000000, 144000000); // 6-12 juta/bulan
        }

        // ENERGY COSTS
        if (str_contains($categoryName, 'electricity') || str_contains($categoryName, 'pln')) {
            return rand(120000000, 180000000); // 10-15 juta/bulan
        }
        if (str_contains($categoryName, 'water') || str_contains($categoryName, 'pdam')) {
            return rand(36000000, 60000000); // 3-5 juta/bulan
        }
        if (str_contains($categoryName, 'fuel') || str_contains($categoryName, 'diesel')) {
            return rand(24000000, 48000000); // 2-4 juta/bulan
        }

        // CLEANING & SUPPLIES
        if (str_contains($categoryName, 'cleaning supplies')) {
            return rand(36000000, 72000000); // 3-6 juta/bulan
        }
        if (str_contains($categoryName, 'guest supplies') || str_contains($categoryName, 'amenities')) {
            return rand(48000000, 96000000); // 4-8 juta/bulan
        }

        // LINEN & TOWELS
        if (str_contains($categoryName, 'linen') || str_contains($categoryName, 'towels')) {
            return rand(60000000, 120000000); // 5-10 juta/bulan
        }

        // LAUNDRY
        if (str_contains($categoryName, 'laundry')) {
            return rand(36000000, 72000000); // 3-6 juta/bulan
        }

        // MAINTENANCE & REPAIRS
        if (str_contains($categoryName, 'repairs') || str_contains($categoryName, 'maintenance')) {
            return rand(48000000, 120000000); // 4-10 juta/bulan
        }

        // TELECOMMUNICATIONS
        if (str_contains($categoryName, 'telecommunications') || str_contains($categoryName, 'telecom')) {
            return rand(12000000, 24000000); // 1-2 juta/bulan
        }

        // PRINTING & STATIONERY
        if (str_contains($categoryName, 'printing') || str_contains($categoryName, 'stationery')) {
            return rand(6000000, 12000000); // 500rb-1juta/bulan
        }

        // UNIFORMS
        if (str_contains($categoryName, 'uniforms') || str_contains($categoryName, 'uniform')) {
            return rand(12000000, 24000000); // 1-2 juta/bulan
        }

        // DECORATIONS
        if (str_contains($categoryName, 'decorations') || str_contains($categoryName, 'flowers')) {
            return rand(12000000, 24000000); // 1-2 juta/bulan
        }

        // ENTERTAINMENT
        if (str_contains($categoryName, 'entertainment') || str_contains($categoryName, 'music')) {
            return rand(12000000, 36000000); // 1-3 juta/bulan
        }

        // TRANSPORTATION
        if (str_contains($categoryName, 'transportation') || str_contains($categoryName, 'transport')) {
            return rand(18000000, 36000000); // 1.5-3 juta/bulan
        }

        // UTENSILS & EQUIPMENT
        if (str_contains($categoryName, 'utensils') || str_contains($categoryName, 'chinaware') ||
            str_contains($categoryName, 'glassware') || str_contains($categoryName, 'tableware')) {
            return rand(24000000, 48000000); // 2-4 juta/bulan
        }

        // KITCHEN FUEL/GAS
        if (str_contains($categoryName, 'kitchen fuel') || str_contains($categoryName, 'gas')) {
            return rand(18000000, 36000000); // 1.5-3 juta/bulan
        }

        // PROFESSIONAL FEES
        if (str_contains($categoryName, 'audit') || str_contains($categoryName, 'legal')) {
            return rand(24000000, 60000000); // 2-5 juta/bulan
        }

        // PERMITS & LICENSES
        if (str_contains($categoryName, 'permits') || str_contains($categoryName, 'licenses')) {
            return rand(12000000, 36000000); // 1-3 juta/bulan
        }

        // OFFICE SUPPLIES
        if (str_contains($categoryName, 'office supplies')) {
            return rand(6000000, 18000000); // 500rb-1.5juta/bulan
        }

        // BANK CHARGES
        if (str_contains($categoryName, 'bank charges')) {
            return rand(3600000, 12000000); // 300rb-1juta/bulan
        }

        // TRAVEL EXPENSES
        if (str_contains($categoryName, 'travel')) {
            return rand(12000000, 36000000); // 1-3 juta/bulan
        }

        // WASTE REMOVAL
        if (str_contains($categoryName, 'waste')) {
            return rand(6000000, 12000000); // 500rb-1juta/bulan
        }

        // CONTRACT LABOR
        if (str_contains($categoryName, 'contract labor') || str_contains($categoryName, 'outsourcing')) {
            return rand(60000000, 120000000); // 5-10 juta/bulan
        }

        // OPERATING SUPPLIES (Default for misc)
        if (str_contains($categoryName, 'operating supplies') || str_contains($categoryName, 'supplies')) {
            return rand(12000000, 36000000); // 1-3 juta/bulan
        }

        // ELECTRICAL & MECHANICAL
        if (str_contains($categoryName, 'electrical') || str_contains($categoryName, 'mechanical')) {
            return rand(24000000, 72000000); // 2-6 juta/bulan
        }

        // PAINTING & DECORATION
        if (str_contains($categoryName, 'painting') || str_contains($categoryName, 'decoration')) {
            return rand(18000000, 48000000); // 1.5-4 juta/bulan
        }

        // Default fallback
        return rand(12000000, 48000000); // 1-4 juta/bulan
    }
}
