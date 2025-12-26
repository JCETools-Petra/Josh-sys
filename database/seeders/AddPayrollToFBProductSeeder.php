<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialCategory;

class AddPayrollToFBProductSeeder extends Seeder
{
    public function run()
    {
        // Find ALL F&B Product departments across all properties
        $fbProducts = FinancialCategory::where('name', 'F&B Product (Kitchen)')
            ->whereNull('parent_id')
            ->get();

        if ($fbProducts->isEmpty()) {
            $this->command->error('No F&B Product departments found!');
            return;
        }

        $this->command->info("Found {$fbProducts->count()} F&B Product department(s)");
        $this->command->info(str_repeat('=', 80));

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($fbProducts as $fbProduct) {
            $property = \App\Models\Property::find($fbProduct->property_id);
            $propertyName = $property ? $property->name : "Unknown (ID: {$fbProduct->property_id})";

            $this->command->info("\nProcessing: {$propertyName}");
            $this->command->info("F&B Product ID: {$fbProduct->id}");

            // Get property_id from the department
            $propertyId = $fbProduct->property_id;

            // Check if PAYROLL section already exists
            $existingPayroll = FinancialCategory::where('parent_id', $fbProduct->id)
                ->where('name', 'PAYROLL & RELATED EXPENSES')
                ->first();

            if ($existingPayroll) {
                $this->command->warn("  ⊗ PAYROLL & RELATED EXPENSES already exists - skipped");
                $totalSkipped++;
                continue; // Skip to next property
            }

            // Get highest sort_order for F&B Product children
            $maxSortOrder = FinancialCategory::where('parent_id', $fbProduct->id)
                ->max('sort_order') ?? 0;

            // Create PAYROLL & RELATED EXPENSES section
            $this->command->info('  Creating PAYROLL & RELATED EXPENSES section...');
            $payrollSection = FinancialCategory::create([
                'property_id' => $propertyId,
                'parent_id' => $fbProduct->id,
                'name' => 'PAYROLL & RELATED EXPENSES',
                'code' => 'FB_PAYROLL_' . $propertyId, // Make code unique per property
                'type' => 'expense',
                'is_payroll' => false, // This is the section header, not actual payroll
                'sort_order' => $maxSortOrder + 10,
            ]);

            $this->command->info("  Created section (ID: {$payrollSection->id})");

            // Define payroll sub-categories
            $payrollCategories = [
                ['name' => 'SALARIES & WAGES', 'code' => 'FB_SALARIES', 'is_payroll' => true],
                ['name' => 'LEBARAN BONUS', 'code' => 'FB_LEBARAN', 'is_payroll' => true],
                ['name' => 'EMPLOYEE TRANSPORTATION', 'code' => 'FB_TRANSPORT', 'is_payroll' => true],
                ['name' => 'MEDICAL EXPENSES', 'code' => 'FB_MEDICAL', 'is_payroll' => true],
                ['name' => 'STAFF MEALS', 'code' => 'FB_MEALS', 'is_payroll' => true],
                ['name' => 'JAMSOSTEK', 'code' => 'FB_JAMSOSTEK', 'is_payroll' => true],
                ['name' => 'TEMPORARY WORKERS', 'code' => 'FB_TEMP', 'is_payroll' => true],
                ['name' => 'STAFF AWARD', 'code' => 'FB_AWARD', 'is_payroll' => true],
            ];

            $this->command->info('  Creating sub-categories...');
            $sortOrder = 10;

            foreach ($payrollCategories as $cat) {
                $category = FinancialCategory::create([
                    'property_id' => $propertyId,
                    'parent_id' => $payrollSection->id,
                    'name' => $cat['name'],
                    'code' => $cat['code'] . '_' . $propertyId, // Make code unique per property
                    'type' => 'expense',
                    'is_payroll' => $cat['is_payroll'],
                    'sort_order' => $sortOrder,
                ]);

                $this->command->info("    ✓ {$cat['name']} (ID: {$category->id})");
                $sortOrder += 10;
            }

            $this->command->info("  ✅ Success! Created " . count($payrollCategories) . " sub-categories for {$propertyName}");
            $totalCreated++;
        }

        // Summary
        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("SUMMARY:");
        $this->command->info("  Properties processed: {$fbProducts->count()}");
        $this->command->info("  Successfully created: {$totalCreated}");
        $this->command->info("  Skipped (already exists): {$totalSkipped}");
        $this->command->info("✅ Done!");
    }
}
