<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use App\Models\Property;
use Illuminate\Database\Seeder;

class FinancialCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates a comprehensive USALI-compliant financial category structure
     * for all properties in the system.
     */
    public function run(): void
    {
        // Get all properties
        $properties = Property::all();

        foreach ($properties as $property) {
            $this->seedForProperty($property->id);
        }
    }

    /**
     * Seed financial categories for a specific property.
     */
    private function seedForProperty(int $propertyId): void
    {
        // Clear existing categories for this property
        FinancialCategory::where('property_id', $propertyId)->delete();

        $sortOrder = 1;

        // ============================================================
        // A. REVENUE SECTION
        // ============================================================

        // 1. Room Revenue (Auto-calculated from DailyIncome)
        $roomRevenue = $this->createCategory($propertyId, null, 'Room Revenue', 'ROOM_REV', 'revenue', false, $sortOrder++);

        // 2. F&B Revenue (Auto-calculated from DailyIncome)
        $fnbRevenue = $this->createCategory($propertyId, null, 'F&B Revenue', 'FNB_REV', 'revenue', false, $sortOrder++);

        // 3. MICE Revenue (Auto-calculated from Bookings)
        $miceRevenue = $this->createCategory($propertyId, null, 'MICE Revenue', 'MICE_REV', 'revenue', false, $sortOrder++);

        // 4. Other Revenue
        $otherRevenue = $this->createCategory($propertyId, null, 'Other Revenue', null, 'revenue', false, $sortOrder++);

        // ============================================================
        // B. EXPENSES SECTION - Departmental Breakdown
        // ============================================================

        // -------------------- 1. FRONT OFFICE --------------------
        $frontOffice = $this->createCategory($propertyId, null, 'Front Office', null, 'expense', false, $sortOrder++);

        // Front Office - Payroll & Related Expenses
        $foPayroll = $this->createCategory($propertyId, $frontOffice->id, 'PAYROLL & RELATED EXPENSES', null, 'expense', true, 1);
        $this->createCategory($propertyId, $foPayroll->id, 'SALARIES & WAGES', null, 'expense', false, 1);
        $this->createCategory($propertyId, $foPayroll->id, 'LEBARAN BONUS', null, 'expense', false, 2);
        $this->createCategory($propertyId, $foPayroll->id, 'EMPLOYEE TRANSPORTATION', null, 'expense', false, 3);
        $this->createCategory($propertyId, $foPayroll->id, 'MEDICAL EXPENSES', null, 'expense', false, 4);
        $this->createCategory($propertyId, $foPayroll->id, 'STAFF MEALS', null, 'expense', false, 5);
        $this->createCategory($propertyId, $foPayroll->id, 'JAMSOSTEK', null, 'expense', false, 6);
        $this->createCategory($propertyId, $foPayroll->id, 'TEMPORARY WORKERS', null, 'expense', false, 7);
        $this->createCategory($propertyId, $foPayroll->id, 'STAFF AWARD', null, 'expense', false, 8);

        // Front Office - Total Staff Cost (calculated)
        $this->createCategory($propertyId, $frontOffice->id, 'TOTAL STAFF COST', null, 'calculated', false, 2);

        // Front Office - Operational Expenses
        $foOperational = $this->createCategory($propertyId, $frontOffice->id, 'Operational Expenses', null, 'expense', false, 3);
        $this->createCategory($propertyId, $foOperational->id, 'Printing & Stationery', null, 'expense', false, 1);
        $this->createCategory($propertyId, $foOperational->id, 'Telephone & Facsimile', null, 'expense', false, 2);
        $this->createCategory($propertyId, $foOperational->id, 'Internet', null, 'expense', false, 3);
        $this->createCategory($propertyId, $foOperational->id, 'Advertising Rooms', null, 'expense', false, 4);
        $this->createCategory($propertyId, $foOperational->id, 'Advertising F & B', null, 'expense', false, 5);
        $this->createCategory($propertyId, $foOperational->id, 'Join Marketing Expenses', null, 'expense', false, 6);
        $this->createCategory($propertyId, $foOperational->id, 'Entertainment Outside Hotel', null, 'expense', false, 7);
        $this->createCategory($propertyId, $foOperational->id, 'Welcome drink', null, 'expense', false, 8);
        $this->createCategory($propertyId, $foOperational->id, 'Gifts & Promotion', null, 'expense', false, 9);
        $this->createCategory($propertyId, $foOperational->id, 'Guest Promotion', null, 'expense', false, 10);
        $this->createCategory($propertyId, $foOperational->id, 'OTA Promotion', null, 'expense', false, 11);
        $this->createCategory($propertyId, $foOperational->id, 'Brochures', null, 'expense', false, 12);
        $this->createCategory($propertyId, $foOperational->id, 'Transportation', null, 'expense', false, 13);
        $this->createCategory($propertyId, $foOperational->id, 'Fuel Cost', null, 'expense', false, 14);
        $this->createCategory($propertyId, $foOperational->id, 'Office Supplies', null, 'expense', false, 15);
        $this->createCategory($propertyId, $foOperational->id, 'Tool Kits', null, 'expense', false, 16);
        $this->createCategory($propertyId, $foOperational->id, 'Key Cards', null, 'expense', false, 17);
        $this->createCategory($propertyId, $foOperational->id, 'Advertising Join Corporate Fee', null, 'expense', false, 18);
        $this->createCategory($propertyId, $foOperational->id, 'Others Expenses', null, 'expense', false, 19);

        // Front Office - Total operational Expenses (calculated)
        $this->createCategory($propertyId, $frontOffice->id, 'Total operational Expenses', null, 'calculated', false, 4);

        // -------------------- 2. HOUSEKEEPING --------------------
        $housekeeping = $this->createCategory($propertyId, null, 'Housekeeping', null, 'expense', false, $sortOrder++);

        // Housekeeping - Payroll & Related Expenses
        $hkPayroll = $this->createCategory($propertyId, $housekeeping->id, 'PAYROLL & RELATED EXPENSES', null, 'expense', true, 1);
        $this->createCategory($propertyId, $hkPayroll->id, 'SALARIES & WAGES', null, 'expense', false, 1);
        $this->createCategory($propertyId, $hkPayroll->id, 'LEBARAN BONUS', null, 'expense', false, 2);
        $this->createCategory($propertyId, $hkPayroll->id, 'EMPLOYEE TRANSPORTATION', null, 'expense', false, 3);
        $this->createCategory($propertyId, $hkPayroll->id, 'MEDICAL EXPENSES', null, 'expense', false, 4);
        $this->createCategory($propertyId, $hkPayroll->id, 'STAFF MEALS', null, 'expense', false, 5);
        $this->createCategory($propertyId, $hkPayroll->id, 'JAMSOSTEK', null, 'expense', false, 6);
        $this->createCategory($propertyId, $hkPayroll->id, 'TEMPORARY WORKERS', null, 'expense', false, 7);
        $this->createCategory($propertyId, $hkPayroll->id, 'STAFF AWARD', null, 'expense', false, 8);

        // Housekeeping - Total Staff Cost (calculated)
        $this->createCategory($propertyId, $housekeeping->id, 'TOTAL STAFF COST', null, 'calculated', false, 2);

        // Housekeeping - Other Expenses
        $hkOther = $this->createCategory($propertyId, $housekeeping->id, 'OTHER EXPENSES', null, 'expense', false, 3);
        $this->createCategory($propertyId, $hkOther->id, 'Room Amenities', null, 'expense', false, 1);
        $this->createCategory($propertyId, $hkOther->id, 'Guest Transportation', null, 'expense', false, 2);
        $this->createCategory($propertyId, $hkOther->id, 'Laundry Chemical', null, 'expense', false, 3);
        $this->createCategory($propertyId, $hkOther->id, 'Laundry Gas', null, 'expense', false, 4);
        $this->createCategory($propertyId, $hkOther->id, 'Cleaning supplies', null, 'expense', false, 5);
        $this->createCategory($propertyId, $hkOther->id, 'Equipment / Supplies', null, 'expense', false, 6);
        $this->createCategory($propertyId, $hkOther->id, 'Printing & Stationery', null, 'expense', false, 7);
        $this->createCategory($propertyId, $hkOther->id, 'Transportation', null, 'expense', false, 8);
        $this->createCategory($propertyId, $hkOther->id, 'Television Maintance', null, 'expense', false, 9);
        $this->createCategory($propertyId, $hkOther->id, 'Broadbands Internet', null, 'expense', false, 10);
        $this->createCategory($propertyId, $hkOther->id, 'Removal waste', null, 'expense', false, 11);
        $this->createCategory($propertyId, $hkOther->id, 'Postage &Courier', null, 'expense', false, 12);
        $this->createCategory($propertyId, $hkOther->id, 'Contract Service', null, 'expense', false, 13);
        $this->createCategory($propertyId, $hkOther->id, 'Air Conditioners Expenses', null, 'expense', false, 14);
        $this->createCategory($propertyId, $hkOther->id, 'Plumbing & Heating', null, 'expense', false, 15);
        $this->createCategory($propertyId, $hkOther->id, 'Maintenance-Building', null, 'expense', false, 16);
        $this->createCategory($propertyId, $hkOther->id, 'Pest Control', null, 'expense', false, 17);
        $this->createCategory($propertyId, $hkOther->id, 'Bathroom amenities', null, 'expense', false, 18);
        $this->createCategory($propertyId, $hkOther->id, 'Electrical Bulp/Lamp', null, 'expense', false, 19);
        $this->createCategory($propertyId, $hkOther->id, 'In Housek Laundry Supplies', null, 'expense', false, 20);
        $this->createCategory($propertyId, $hkOther->id, 'Cleaning Laundry Supplies', null, 'expense', false, 21);
        $this->createCategory($propertyId, $hkOther->id, 'CHINA GLASSWARE room', null, 'expense', false, 22);
        $this->createCategory($propertyId, $hkOther->id, 'Cleaning Equipment', null, 'expense', false, 23);

        // -------------------- 3. F&B PRODUCT (Kitchen) --------------------
        $fnbProduct = $this->createCategory($propertyId, null, 'F&B Product (Kitchen)', null, 'expense', false, $sortOrder++);

        // F&B Product - OTHER EXPENSES section with -COST items
        $fnbProductOtherExpenses = $this->createCategory($propertyId, $fnbProduct->id, 'OTHER EXPENSES', null, 'expense', false, 1);
        $this->createCategory($propertyId, $fnbProductOtherExpenses->id, '-COST Beverage Revenue', null, 'expense', false, 1);
        $this->createCategory($propertyId, $fnbProductOtherExpenses->id, '-COST Breakfast', null, 'expense', false, 2);
        $this->createCategory($propertyId, $fnbProductOtherExpenses->id, '-COST Lunch', null, 'expense', false, 3);
        $this->createCategory($propertyId, $fnbProductOtherExpenses->id, '-COST Dinner', null, 'expense', false, 4);
        $this->createCategory($propertyId, $fnbProductOtherExpenses->id, '-COST Package', null, 'expense', false, 5);
        $this->createCategory($propertyId, $fnbProductOtherExpenses->id, '-COST MICE', null, 'expense', false, 6);

        // F&B Product - FOOD COST 2026 (calculated)
        $this->createCategory($propertyId, $fnbProduct->id, 'FOOD COST 2026', null, 'calculated', false, 2);

        // F&B Product - Average Cost/cover
        $fnbProductAvgCost = $this->createCategory($propertyId, $fnbProduct->id, 'Average Cost/cover', null, 'expense', false, 3);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-LICENSE &SERTIFICATION', null, 'expense', false, 1);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Decoration', null, 'expense', false, 2);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Cleaning Supplies', null, 'expense', false, 3);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Food Spoilage', null, 'expense', false, 4);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Kitchen Supplies', null, 'expense', false, 5);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Licenses & Permits', null, 'expense', false, 6);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Printing & Stationery', null, 'expense', false, 7);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Telephone & Facsimile', null, 'expense', false, 8);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Linen & Napkins/Tissue', null, 'expense', false, 9);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Equipment Rental & Lease', null, 'expense', false, 10);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Food & Beverage Testing', null, 'expense', false, 11);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Operating Supplies', null, 'expense', false, 12);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Paper And Plastic Supplies', null, 'expense', false, 13);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Guests Supplies', null, 'expense', false, 14);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Advertising & Promotion', null, 'expense', false, 15);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Menu & Chits', null, 'expense', false, 16);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Utensils', null, 'expense', false, 17);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Traveling & Related', null, 'expense', false, 18);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Trash Removal', null, 'expense', false, 19);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Casuals', null, 'expense', false, 20);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Uniform', null, 'expense', false, 21);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Fuel/Gas Steam', null, 'expense', false, 22);
        $this->createCategory($propertyId, $fnbProductAvgCost->id, 'KITCHEN-Other Expenses', null, 'expense', false, 23);

        // -------------------- 4. F&B SERVICE --------------------
        $fnbService = $this->createCategory($propertyId, null, 'F&B Service', null, 'expense', false, $sortOrder++);

        // F&B Service - Payroll & Related Expenses
        $fnbServicePayroll = $this->createCategory($propertyId, $fnbService->id, 'PAYROLL & RELATED EXPENSES', null, 'expense', true, 1);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'SALARIES & WAGES FB SERVICE', null, 'expense', false, 1);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'SALARIES & WAGES FB PRODUCTION', null, 'expense', false, 2);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'LEBARAN BONUS', null, 'expense', false, 3);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'EMPLOYEE TRANSPORTATION', null, 'expense', false, 4);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'MEDICAL EXPENSES', null, 'expense', false, 5);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'STAFF MEALS', null, 'expense', false, 6);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'JAMSOSTEK', null, 'expense', false, 7);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'TEMPORARY WORKERS', null, 'expense', false, 8);
        $this->createCategory($propertyId, $fnbServicePayroll->id, 'STAFF AWARD', null, 'expense', false, 9);

        // F&B Service - Total Staff Cost (calculated)
        $this->createCategory($propertyId, $fnbService->id, 'TOTAL STAFF COST', null, 'calculated', false, 2);

        // F&B Service - Other Expenses
        $fnbServiceOther = $this->createCategory($propertyId, $fnbService->id, 'Other Expenses', null, 'expense', false, 3);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Pest Control', null, 'expense', false, 1);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Decoration', null, 'expense', false, 2);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Cleaning Supplies', null, 'expense', false, 3);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Food Spoilage', null, 'expense', false, 4);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Kitchen Supplies', null, 'expense', false, 5);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Licenses & Permits', null, 'expense', false, 6);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Printing & Stationery', null, 'expense', false, 7);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Telephone & Facsimile', null, 'expense', false, 8);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Linen & Napkins', null, 'expense', false, 9);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Equipment Rental & Lease', null, 'expense', false, 10);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Food & Beverage Testing', null, 'expense', false, 11);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Operating Supplies', null, 'expense', false, 12);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Paper And Plastic Supplies', null, 'expense', false, 13);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Guests Supplies', null, 'expense', false, 14);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Advertising & Promotion', null, 'expense', false, 15);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Music & Entertainment', null, 'expense', false, 16);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Menu & Chits', null, 'expense', false, 17);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Data Processing', null, 'expense', false, 18);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Utensils', null, 'expense', false, 19);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Traveling & Related', null, 'expense', false, 20);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Trash Removal', null, 'expense', false, 21);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Space Rental / Lease', null, 'expense', false, 22);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'FB Service Casuals', null, 'expense', false, 23);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Uniform', null, 'expense', false, 24);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Fuel/Gas Steam', null, 'expense', false, 25);
        $this->createCategory($propertyId, $fnbServiceOther->id, 'Other Expenses', null, 'expense', false, 26);

        // -------------------- 5. POMAC (Property Operation Maintenance & Energy Cost) --------------------
        $pomac = $this->createCategory($propertyId, null, 'POMAC (Property Operation, Maintenance & Energy)', null, 'expense', false, $sortOrder++);

        // POMAC - Payroll & Related Expenses
        $pomacPayroll = $this->createCategory($propertyId, $pomac->id, 'PAYROLL & RELATED EXPENSES', null, 'expense', true, 1);
        $this->createCategory($propertyId, $pomacPayroll->id, 'SALARIES & WAGES', null, 'expense', false, 1);
        $this->createCategory($propertyId, $pomacPayroll->id, 'LEBARAN BONUS', null, 'expense', false, 2);
        $this->createCategory($propertyId, $pomacPayroll->id, 'EMPLOYEE TRANSPORTATION', null, 'expense', false, 3);
        $this->createCategory($propertyId, $pomacPayroll->id, 'MEDICAL EXPENSES', null, 'expense', false, 4);
        $this->createCategory($propertyId, $pomacPayroll->id, 'STAFF MEALS', null, 'expense', false, 5);
        $this->createCategory($propertyId, $pomacPayroll->id, 'JAMSOSTEK', null, 'expense', false, 6);
        $this->createCategory($propertyId, $pomacPayroll->id, 'TEMPORARY WORKERS', null, 'expense', false, 7);
        $this->createCategory($propertyId, $pomacPayroll->id, 'STAFF AWARD', null, 'expense', false, 8);

        // POMAC - Total Staff Cost (calculated)
        $this->createCategory($propertyId, $pomac->id, 'TOTAL STAFF COST', null, 'calculated', false, 2);

        // POMAC - Other Expenses
        $pomacOther = $this->createCategory($propertyId, $pomac->id, 'OTHER EXPENSES', null, 'expense', false, 3);
        $this->createCategory($propertyId, $pomacOther->id, 'Printing & Stationery', null, 'expense', false, 1);
        $this->createCategory($propertyId, $pomacOther->id, 'Telephone & Facsimile', null, 'expense', false, 2);
        $this->createCategory($propertyId, $pomacOther->id, 'Kitchen Equipment', null, 'expense', false, 3);
        $this->createCategory($propertyId, $pomacOther->id, 'Room Key System', null, 'expense', false, 4);
        $this->createCategory($propertyId, $pomacOther->id, 'Electrical Supplies & Bulb', null, 'expense', false, 5);
        $this->createCategory($propertyId, $pomacOther->id, 'Air Condition', null, 'expense', false, 6);
        $this->createCategory($propertyId, $pomacOther->id, 'Plumbing & Piping system', null, 'expense', false, 7);
        $this->createCategory($propertyId, $pomacOther->id, 'Mechanical & Electrical', null, 'expense', false, 8);
        $this->createCategory($propertyId, $pomacOther->id, 'Engineering Equipment & Supplies', null, 'expense', false, 9);
        $this->createCategory($propertyId, $pomacOther->id, 'Swimming Pool Chemicals & Supplie', null, 'expense', false, 10);
        $this->createCategory($propertyId, $pomacOther->id, 'Equipment Repair & Maintenance', null, 'expense', false, 11);
        $this->createCategory($propertyId, $pomacOther->id, 'Parts & Supplies', null, 'expense', false, 12);
        $this->createCategory($propertyId, $pomacOther->id, 'Vehicles/Car', null, 'expense', false, 13);
        $this->createCategory($propertyId, $pomacOther->id, 'Painting', null, 'expense', false, 14);
        $this->createCategory($propertyId, $pomacOther->id, 'Drapes & Carpets', null, 'expense', false, 15);
        $this->createCategory($propertyId, $pomacOther->id, 'Furniture & Fixtures', null, 'expense', false, 16);
        $this->createCategory($propertyId, $pomacOther->id, 'Building Repair', null, 'expense', false, 17);
        $this->createCategory($propertyId, $pomacOther->id, 'Floor & Wall Covering', null, 'expense', false, 18);
        $this->createCategory($propertyId, $pomacOther->id, 'Sewage & Treatment', null, 'expense', false, 19);
        $this->createCategory($propertyId, $pomacOther->id, 'Ground & Landscaping', null, 'expense', false, 20);
        $this->createCategory($propertyId, $pomacOther->id, 'Removal Of Waste', null, 'expense', false, 21);
        $this->createCategory($propertyId, $pomacOther->id, 'Signage', null, 'expense', false, 22);
        $this->createCategory($propertyId, $pomacOther->id, 'Transportation', null, 'expense', false, 23);
        $this->createCategory($propertyId, $pomacOther->id, 'Television & Audio Visual', null, 'expense', false, 24);
        $this->createCategory($propertyId, $pomacOther->id, 'Laundry Equipment', null, 'expense', false, 25);
        $this->createCategory($propertyId, $pomacOther->id, 'Laundry Uniforms', null, 'expense', false, 26);
        $this->createCategory($propertyId, $pomacOther->id, 'Contract Services', null, 'expense', false, 27);
        $this->createCategory($propertyId, $pomacOther->id, 'Lampu', null, 'expense', false, 28);
        $this->createCategory($propertyId, $pomacOther->id, 'Decoration Lamp', null, 'expense', false, 29);
        $this->createCategory($propertyId, $pomacOther->id, 'APAR', null, 'expense', false, 30);

        // POMAC - Total Oper & Maint. (calculated)
        $this->createCategory($propertyId, $pomac->id, 'TOTAL OPER & MAINT.', null, 'calculated', false, 4);

        // POMAC - Energy Cost
        $pomacEnergy = $this->createCategory($propertyId, $pomac->id, 'ENERGY COST', null, 'expense', false, 5);
        $this->createCategory($propertyId, $pomacEnergy->id, 'ELECTRICITY', null, 'expense', false, 1);
        $this->createCategory($propertyId, $pomacEnergy->id, 'FUEL & OIL', null, 'expense', false, 2);
        $this->createCategory($propertyId, $pomacEnergy->id, 'WATER', null, 'expense', false, 3);

        // -------------------- 6. ACCOUNTING & GENERAL (A&G) --------------------
        $accountingGeneral = $this->createCategory($propertyId, null, 'Accounting & General (A&G)', null, 'expense', false, $sortOrder++);

        // A&G - Payroll & Related Expenses
        $agPayroll = $this->createCategory($propertyId, $accountingGeneral->id, 'PAYROLL & RELATED EXPENSES', null, 'expense', true, 1);
        $this->createCategory($propertyId, $agPayroll->id, 'SALARIES & WAGES', null, 'expense', false, 1);
        $this->createCategory($propertyId, $agPayroll->id, 'LEBARAN BONUS', null, 'expense', false, 2);
        $this->createCategory($propertyId, $agPayroll->id, 'EMPLOYEE TRANSPORTATION', null, 'expense', false, 3);
        $this->createCategory($propertyId, $agPayroll->id, 'MEDICAL EXPENSES', null, 'expense', false, 4);
        $this->createCategory($propertyId, $agPayroll->id, 'STAFF MEALS', null, 'expense', false, 5);
        $this->createCategory($propertyId, $agPayroll->id, 'JAMSOSTEK', null, 'expense', false, 6);
        $this->createCategory($propertyId, $agPayroll->id, 'TEMPORARY WORKERS', null, 'expense', false, 7);
        $this->createCategory($propertyId, $agPayroll->id, 'STAFF AWARD', null, 'expense', false, 8);

        // A&G - Total Staff Expenses (calculated)
        $this->createCategory($propertyId, $accountingGeneral->id, 'TOTAL STAFF Expenses', null, 'calculated', false, 2);

        // A&G - Other Expenses
        $agOther = $this->createCategory($propertyId, $accountingGeneral->id, 'OTHER EXPENSES', null, 'expense', false, 3);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Printing & Stationery', null, 'expense', false, 1);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Telephone & Facsimile', null, 'expense', false, 2);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Courier & Postage', null, 'expense', false, 3);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Advertising F & B', null, 'expense', false, 4);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Join Marketing Expenses', null, 'expense', false, 5);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Entertainment Outside Hotel', null, 'expense', false, 6);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Gifts & Promotion', null, 'expense', false, 7);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Trade Shows', null, 'expense', false, 8);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Transportation Sales call', null, 'expense', false, 9);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Travelling Cost', null, 'expense', false, 10);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Photo & Graphics', null, 'expense', false, 11);
        $this->createCategory($propertyId, $agOther->id, 'ACT-Office Supplies', null, 'expense', false, 12);
        $this->createCategory($propertyId, $agOther->id, 'ACT Kits', null, 'expense', false, 13);
        $this->createCategory($propertyId, $agOther->id, 'ACT-LINCESES', null, 'expense', false, 14);
        $this->createCategory($propertyId, $agOther->id, 'ACT- Corporate entertainment', null, 'expense', false, 15);
        $this->createCategory($propertyId, $agOther->id, 'act-Others Expenses', null, 'expense', false, 16);

        // -------------------- 7. SALES & MARKETING (MICE) --------------------
        $salesMarketing = $this->createCategory($propertyId, null, 'Sales & Marketing (MICE)', null, 'expense', false, $sortOrder++);

        // Sales & Marketing - Payroll & Related Expenses
        $smPayroll = $this->createCategory($propertyId, $salesMarketing->id, 'PAYROLL & RELATED EXPENSES', null, 'expense', true, 1);
        $this->createCategory($propertyId, $smPayroll->id, 'SALARIES & WAGES', null, 'expense', false, 1);
        $this->createCategory($propertyId, $smPayroll->id, 'LEBARAN BONUS', null, 'expense', false, 2);
        $this->createCategory($propertyId, $smPayroll->id, 'EMPLOYEE TRANSPORTATION', null, 'expense', false, 3);
        $this->createCategory($propertyId, $smPayroll->id, 'MEDICAL EXPENSES', null, 'expense', false, 4);
        $this->createCategory($propertyId, $smPayroll->id, 'STAFF MEALS', null, 'expense', false, 5);
        $this->createCategory($propertyId, $smPayroll->id, 'JAMSOSTEK', null, 'expense', false, 6);
        $this->createCategory($propertyId, $smPayroll->id, 'TEMPORARY WORKERS', null, 'expense', false, 7);
        $this->createCategory($propertyId, $smPayroll->id, 'STAFF AWARD', null, 'expense', false, 8);

        // Sales & Marketing - Total Staff Expenses (calculated)
        $this->createCategory($propertyId, $salesMarketing->id, 'TOTAL STAFF Expenses', null, 'calculated', false, 2);

        // Sales & Marketing - Other Expenses
        $smOther = $this->createCategory($propertyId, $salesMarketing->id, 'OTHER EXPENSES', null, 'expense', false, 3);
        $this->createCategory($propertyId, $smOther->id, 'SM-Printing & Stationery', null, 'expense', false, 1);
        $this->createCategory($propertyId, $smOther->id, 'SM-Telephone & Facsimile', null, 'expense', false, 2);
        $this->createCategory($propertyId, $smOther->id, 'SM-Courier & Postage', null, 'expense', false, 3);
        $this->createCategory($propertyId, $smOther->id, 'SM-Advertising Rooms', null, 'expense', false, 4);
        $this->createCategory($propertyId, $smOther->id, 'SM-Advertising F & B', null, 'expense', false, 5);
        $this->createCategory($propertyId, $smOther->id, 'SM-Join Marketing Expenses', null, 'expense', false, 6);
        $this->createCategory($propertyId, $smOther->id, 'SM-Entertainment Outside Hotel', null, 'expense', false, 7);
        $this->createCategory($propertyId, $smOther->id, 'SM-Familiarization Table manner', null, 'expense', false, 8);
        $this->createCategory($propertyId, $smOther->id, 'SM-Gifts & Promotion', null, 'expense', false, 9);
        $this->createCategory($propertyId, $smOther->id, 'SM-Guest Promotion', null, 'expense', false, 10);
        $this->createCategory($propertyId, $smOther->id, 'SM-Trade Shows', null, 'expense', false, 11);
        $this->createCategory($propertyId, $smOther->id, 'SM-Brochures', null, 'expense', false, 12);
        $this->createCategory($propertyId, $smOther->id, 'SM-Transportation Sales call', null, 'expense', false, 13);
        $this->createCategory($propertyId, $smOther->id, 'SM-Travelling Cost', null, 'expense', false, 14);
        $this->createCategory($propertyId, $smOther->id, 'SM-Photo & Graphics', null, 'expense', false, 15);
        $this->createCategory($propertyId, $smOther->id, 'SM-Office Supplies', null, 'expense', false, 16);
        $this->createCategory($propertyId, $smOther->id, 'SM-Sales & Marketing Kits', null, 'expense', false, 17);
        $this->createCategory($propertyId, $smOther->id, 'SM-Print Media - Magazines', null, 'expense', false, 18);
        $this->createCategory($propertyId, $smOther->id, 'SM-Advertising Join Corporate Fee', null, 'expense', false, 19);
        $this->createCategory($propertyId, $smOther->id, 'SM-Others Expenses', null, 'expense', false, 20);
    }

    /**
     * Helper method to create a category.
     */
    private function createCategory(
        int $propertyId,
        ?int $parentId,
        string $name,
        ?string $code,
        string $type,
        bool $isPayroll,
        int $sortOrder
    ): FinancialCategory {
        return FinancialCategory::create([
            'property_id' => $propertyId,
            'parent_id' => $parentId,
            'name' => $name,
            'code' => $code,
            'type' => $type,
            'is_payroll' => $isPayroll,
            'sort_order' => $sortOrder,
        ]);
    }
}
