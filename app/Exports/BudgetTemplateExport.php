<?php

namespace App\Exports;

use App\Models\FinancialCategory;
use App\Models\Property;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BudgetTemplateExport implements FromArray, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    protected $propertyId;
    protected $year;
    protected $property;
    protected $departmentColors = [
        'Front Office' => 'E7E6F7',
        'Housekeeping' => 'FCE4D6',
        'F&B Product (Kitchen)' => 'E2F0D9',
        'F&B Service' => 'DEEBF7',
        'POMAC (Property Operation, Maintenance & Energy Cost)' => 'FFF2CC',
        'Accounting & General' => 'F4B084',
        'Sales & Marketing (MICE)' => 'C5E0B4',
    ];

    public function __construct(int $propertyId, int $year)
    {
        $this->propertyId = $propertyId;
        $this->year = $year;
        $this->property = Property::find($propertyId);
    }

    public function array(): array
    {
        $propertyName = $this->property ? strtoupper($this->property->name) : 'PROPERTY';

        // Title and header rows
        $data = [
            ['BUDGET TEMPLATE - ' . $propertyName, '', '', '', '', '', '', '', '', '', '', '', '', '', ''], // Baris 1: Judul
            ['Tahun: ' . $this->year, '', '', '', '', '', '', '', '', '', '', '', '', '', ''], // Baris 2: Tahun
            [''], // Baris 3: FIXED (Menggunakan [''] agar baris ini tidak hilang/skip)
            ['Category ID', 'Department', 'Category Name', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'], // Baris 4: Header Data
        ];

        // Get all expense departments only (top-level categories with type='expense')
        $departments = FinancialCategory::forProperty($this->propertyId)
            ->whereNull('parent_id')
            ->where('type', 'expense')
            ->orderBy('sort_order')
            ->get();

        foreach ($departments as $department) {
            // Add department header row
            $data[] = [
                '', // Category ID
                strtoupper($department->name), // Department
                '', // Category Name
                '', '', '', '', '', '', '', '', '', '', '', '' // Empty months
            ];

            // Get all expense categories under this department
            $this->addCategoriesRecursive($data, $department, 1);

            // Add empty row between departments
            $data[] = ['']; // FIXED: Gunakan [''] untuk pemisah antar departemen
        }

        return $data;
    }

    private function addCategoriesRecursive(&$data, $parent, $level)
    {
        $children = FinancialCategory::forProperty($this->propertyId)
            ->where('parent_id', $parent->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($children as $category) {
            // Check if this category has children
            $hasChildren = $category->children()->exists();

            if ($hasChildren) {
                // Add section header
                $indent = str_repeat('  ', $level);
                $data[] = [
                    '', // No Category ID for section headers
                    '', // Department
                    $indent . '▶ ' . strtoupper($category->name), // Category Name with marker
                    '', '', '', '', '', '', '', '', '', '', '', '' // Empty months
                ];

                // Recurse to children
                $this->addCategoriesRecursive($data, $category, $level + 1);
            } else {
                // Only add expense type leaf nodes (input-eligible categories)
                if ($category->type === 'expense') {
                    $indent = str_repeat('  ', $level);

                    $data[] = [
                        $category->id, // Category ID
                        $this->getDepartmentName($category), // Department
                        $indent . $category->name, // Category Name with indentation
                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 // 12 months with default 0
                    ];
                }
            }
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Title row
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Year info row
        $sheet->mergeCells('A2:O2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Header row styling (Baris 4)
        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(25);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Category ID
            'B' => 35,  // Department
            'C' => 50,  // Category Name
            'D' => 12,  // January
            'E' => 12,  // February
            'F' => 12,  // March
            'G' => 12,  // April
            'H' => 12,  // May
            'I' => 12,  // June
            'J' => 12,  // July
            'K' => 12,  // August
            'L' => 12,  // September
            'M' => 12,  // October
            'N' => 12,  // November
            'O' => 12,  // December
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $currentDepartment = '';
                $departmentColor = 'FFFFFF';

                // Start from row 5 (after headers)
                for ($row = 5; $row <= $highestRow; $row++) {
                    $categoryId = $sheet->getCell("A{$row}")->getValue();
                    $departmentCell = $sheet->getCell("B{$row}")->getValue();
                    $categoryCell = $sheet->getCell("C{$row}")->getValue();

                    // Check if this is a department header row (has department name but no category ID)
                    if (!empty($departmentCell) && empty($categoryId) && empty($categoryCell)) {
                        $currentDepartment = $departmentCell;
                        $departmentColor = $this->getDepartmentColor($departmentCell);

                        // Style department header
                        $sheet->mergeCells("B{$row}:O{$row}");
                        $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                                'color' => ['rgb' => '000000'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $departmentColor],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => Border::BORDER_MEDIUM,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                        ]);
                        $sheet->getRowDimension($row)->setRowHeight(22);
                    }
                    // Check if this is a section header (starts with ▶)
                    elseif (!empty($categoryCell) && strpos($categoryCell, '▶') !== false) {
                        $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'italic' => true,
                                'size' => 10,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $this->adjustColorBrightness($departmentColor, 30)],
                            ],
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '808080'],
                                ],
                            ],
                        ]);
                    }
                    // Regular category rows (has category ID)
                    elseif (!empty($categoryId)) {
                        // Apply light background
                        $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFFFFF'],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'D0D0D0'],
                                ],
                            ],
                        ]);

                        // Number format for month columns (D to O)
                        $sheet->getStyle("D{$row}:O{$row}")->getNumberFormat()
                            ->setFormatCode('#,##0');

                        // Right align month values
                        $sheet->getStyle("D{$row}:O{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                }

                // Freeze panes at row 5 (after header)
                $sheet->freezePane('A5');

                // Auto-filter on header row
                $sheet->setAutoFilter('A4:O4');
            },
        ];
    }

    public function title(): string
    {
        return 'Budget ' . $this->year;
    }

    private function getDepartmentName(FinancialCategory $category): string
    {
        $root = $category;
        while ($root->parent) {
            $root = $root->parent;
        }
        return $root->name;
    }

    private function getDepartmentColor(string $departmentName): string
    {
        foreach ($this->departmentColors as $key => $color) {
            if (stripos($departmentName, $key) !== false || stripos($key, $departmentName) !== false) {
                return $color;
            }
        }
        return 'E0E0E0'; // Default gray
    }

    private function adjustColorBrightness(string $hexColor, int $percent): string
    {
        $hex = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, $r + (255 - $r) * ($percent / 100));
        $g = min(255, $g + (255 - $g) * ($percent / 100));
        $b = min(255, $b + (255 - $b) * ($percent / 100));

        return sprintf('%02X%02X%02X', $r, $g, $b);
    }
}