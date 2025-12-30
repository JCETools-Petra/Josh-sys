<?php

namespace App\Imports;

use App\Models\FinancialEntry;
use App\Models\FinancialCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class BudgetTemplateImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $propertyId;
    protected $year;
    
    // Property untuk tracking status (Wajib ada untuk Controller)
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    
    protected $currentRow; 

    public function __construct(int $propertyId, int $year)
    {
        $this->propertyId = $propertyId;
        $this->year = $year;
        $this->currentRow = config('hotelier.import.budget_template_start_row') + 1;
    }

    public function headingRow(): int
    {
        return config('hotelier.import.budget_template_start_row');
    }

    public function model(array $row)
    {
        $rowNumber = $this->currentRow++;
        $csvCategoryId = $row['category_id'] ?? null;
        $csvCategoryName = $row['category_name'] ?? '';
        $csvDepartmentName = $row['department'] ?? ''; // Kolom Department dari CSV

        // 1. Silent Skip: Lewati baris header/judul
        if (empty($csvCategoryId) && empty($csvCategoryName)) {
            return null;
        }

        $category = null;

        // 2. Strategi A: Cari berdasarkan ID (Prioritas Utama)
        // Kita tetap cek department_id atau hierarki jika memungkinkan untuk keamanan ganda
        if (!empty($csvCategoryId)) {
            $category = FinancialCategory::where('id', $csvCategoryId)
                ->where('property_id', $this->propertyId)
                ->first();
        }

        // 3. Strategi B: Cari berdasarkan NAMA & DEPARTEMEN (Recursive Parent Check)
        if (!$category && !empty($csvCategoryName)) {
            $cleanName = trim($csvCategoryName);
            $cleanDept = trim($csvDepartmentName);

            // Ambil semua kategori dengan nama yang sama di properti ini
            $candidates = FinancialCategory::where('property_id', $this->propertyId)
                ->where('name', $cleanName)
                ->with('parent.parent') // Load sampai 2 level ke atas (Item -> Sub -> Dept)
                ->get();

            foreach ($candidates as $candidate) {
                // Cek Level 1: Apakah Parent langsungnya adalah Departemen? (Contoh: Operational -> Printing)
                $parentName = $candidate->parent ? trim($candidate->parent->name) : '';
                
                // Cek Level 2: Apakah Grandparent-nya adalah Departemen? (Contoh: Front Office -> Payroll -> Salaries)
                $grandParentName = ($candidate->parent && $candidate->parent->parent) 
                                    ? trim($candidate->parent->parent->name) 
                                    : '';

                // Logika Pencocokan:
                // Apakah nama departemen di CSV cocok dengan Parent ATAU Grandparent di Database?
                if (strcasecmp($parentName, $cleanDept) === 0 || strcasecmp($grandParentName, $cleanDept) === 0) {
                    $category = $candidate;
                    break; // Ketemu yang pas!
                }
            }
        }

        // 4. Jika Kategori Tetap Tidak Ditemukan
        if (!$category) {
            $msg = "Row {$rowNumber}: Kategori tidak ditemukan. ID: '{$csvCategoryId}', Nama: '{$csvCategoryName}', Dept: '{$csvDepartmentName}'";
            $this->errors[] = $msg;
            $this->skippedCount++;
            return null;
        }

        // 5. Validasi Tipe
        if ($category->type !== 'expense') {
            return null;
        }

        // 6. Proses Data Bulanan
        $months = [
            1 => 'january', 2 => 'february', 3 => 'march', 4 => 'april',
            5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august',
            9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december',
        ];

        foreach ($months as $monthNumber => $monthName) {
            $rawValue = $row[$monthName] ?? 0;
            
            // Cleaning Value
            $budgetValue = 0;
            if (!is_null($rawValue) && $rawValue !== '') {
                if (is_numeric($rawValue)) {
                    $budgetValue = (float) $rawValue;
                } elseif (is_string($rawValue)) {
                    // Bersihkan karakter non-numeric (Rp, spasi, dll)
                    // Sisakan angka, titik, dan minus
                    $cleanValue = preg_replace('/[^0-9.\-]/', '', $rawValue);
                    $budgetValue = (float) $cleanValue;
                }
            }

            // Simpan ke Database
            FinancialEntry::updateOrCreate(
                [
                    'property_id' => $this->propertyId,
                    'financial_category_id' => $category->id, 
                    'year' => $this->year,
                    'month' => $monthNumber,
                ],
                [
                    'budget_value' => $budgetValue,
                ]
            );
            
            $this->importedCount++;
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable', 
        ];
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}