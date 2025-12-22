<?php

namespace App\Exports;

use App\Services\FinancialReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class PnLExport implements WithMultipleSheets
{
    protected $propertyId;
    protected $propertyName;
    protected $year;
    protected $month;
    protected $pnlData;
    protected $kpis;
    protected $comparative;
    protected $financialService;

    public function __construct(int $propertyId, string $propertyName, int $year, int $month)
    {
        $this->propertyId = $propertyId;
        $this->propertyName = $propertyName;
        $this->year = $year;
        $this->month = $month;
        $this->financialService = app(FinancialReportService::class);

        // Pre-load data
        $this->pnlData = $this->financialService->getPnL($propertyId, $year, $month);
        $this->kpis = $this->financialService->getKPIs($propertyId, $year, $month);
        $this->comparative = $this->financialService->getComparativeAnalysis($propertyId, $year, $month);
    }

    public function sheets(): array
    {
        return [
            new PnLSummarySheet($this->propertyName, $this->year, $this->month, $this->pnlData),
            new PnLDetailSheet($this->propertyName, $this->year, $this->month, $this->pnlData),
            new KPISheet($this->propertyName, $this->year, $this->month, $this->kpis),
            new ComparativeSheet($this->propertyName, $this->year, $this->month, $this->comparative),
        ];
    }
}

// Summary Sheet
class PnLSummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $propertyName;
    protected $year;
    protected $month;
    protected $pnlData;

    public function __construct(string $propertyName, int $year, int $month, array $pnlData)
    {
        $this->propertyName = $propertyName;
        $this->year = $year;
        $this->month = $month;
        $this->pnlData = $pnlData;
    }

    public function collection()
    {
        $data = new Collection();

        // Header
        $data->push([
            'P&L STATEMENT - ' . $this->propertyName,
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
        $data->push([
            'Period: ' . \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y'),
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
        $data->push(['']); // Empty row

        // Revenue Section
        $data->push([
            'REVENUE',
            'Current Actual',
            'Current Budget',
            'Current Variance',
            'YTD Actual',
            'YTD Budget',
            'YTD Variance'
        ]);

        foreach ($this->pnlData['categories'] as $category) {
            if ($category['type'] === 'revenue') {
                $this->addCategoryRows($data, $category, 0);
            }
        }

        $totals = $this->pnlData['totals'];
        $data->push([
            'TOTAL REVENUE',
            $totals['total_revenue']['actual_current'],
            $totals['total_revenue']['budget_current'],
            $totals['total_revenue']['variance_current'],
            $totals['total_revenue']['actual_ytd'],
            $totals['total_revenue']['budget_ytd'],
            $totals['total_revenue']['variance_ytd'],
        ]);

        $data->push(['']); // Empty row

        // Expense Section
        $data->push([
            'EXPENSES',
            'Current Actual',
            'Current Budget',
            'Current Variance',
            'YTD Actual',
            'YTD Budget',
            'YTD Variance'
        ]);

        foreach ($this->pnlData['categories'] as $category) {
            if ($category['type'] === 'expense') {
                $this->addCategoryRows($data, $category, 0);
            }
        }

        $data->push([
            'TOTAL EXPENSES',
            $totals['total_expenses']['actual_current'],
            $totals['total_expenses']['budget_current'],
            $totals['total_expenses']['variance_current'],
            $totals['total_expenses']['actual_ytd'],
            $totals['total_expenses']['budget_ytd'],
            $totals['total_expenses']['variance_ytd'],
        ]);

        $data->push(['']); // Empty row

        // GOP
        $data->push([
            'GROSS OPERATING PROFIT',
            $totals['gross_operating_profit']['actual_current'],
            $totals['gross_operating_profit']['budget_current'],
            $totals['gross_operating_profit']['variance_current'],
            $totals['gross_operating_profit']['actual_ytd'],
            $totals['gross_operating_profit']['budget_ytd'],
            $totals['gross_operating_profit']['variance_ytd'],
        ]);

        return $data;
    }

    private function addCategoryRows(Collection $data, array $category, int $level): void
    {
        $indent = str_repeat('  ', $level);
        $name = $indent . $category['name'];

        if ($category['code']) {
            $name .= ' (Auto)';
        }

        $data->push([
            $name,
            $category['actual_current'],
            $category['budget_current'],
            $category['variance_current'],
            $category['actual_ytd'],
            $category['budget_ytd'],
            $category['variance_ytd'],
        ]);

        foreach ($category['children'] ?? [] as $child) {
            $this->addCategoryRows($data, $child, $level + 1);
        }
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
        ];
    }

    public function title(): string
    {
        return 'P&L Summary';
    }
}

// Detail Sheet
class PnLDetailSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $propertyName;
    protected $year;
    protected $month;
    protected $pnlData;

    public function __construct(string $propertyName, int $year, int $month, array $pnlData)
    {
        $this->propertyName = $propertyName;
        $this->year = $year;
        $this->month = $month;
        $this->pnlData = $pnlData;
    }

    public function collection()
    {
        $data = new Collection();

        foreach ($this->pnlData['categories'] as $category) {
            $this->addAllCategories($data, $category, '');
        }

        return $data;
    }

    private function addAllCategories(Collection $data, array $category, string $parentPath): void
    {
        $path = $parentPath ? $parentPath . ' > ' . $category['name'] : $category['name'];

        $data->push([
            $path,
            $category['type'],
            $category['is_payroll'] ? 'Yes' : 'No',
            $category['actual_current'],
            $category['budget_current'],
            $category['variance_current'],
            $category['actual_ytd'],
            $category['budget_ytd'],
            $category['variance_ytd'],
        ]);

        foreach ($category['children'] ?? [] as $child) {
            $this->addAllCategories($data, $child, $path);
        }
    }

    public function headings(): array
    {
        return [
            'Category Path',
            'Type',
            'Payroll',
            'Current Actual',
            'Current Budget',
            'Current Variance',
            'YTD Actual',
            'YTD Budget',
            'YTD Variance',
        ];
    }

    public function title(): string
    {
        return 'Detail';
    }
}

// KPI Sheet
class KPISheet implements FromCollection, WithHeadings, WithTitle
{
    protected $propertyName;
    protected $year;
    protected $month;
    protected $kpis;

    public function __construct(string $propertyName, int $year, int $month, array $kpis)
    {
        $this->propertyName = $propertyName;
        $this->year = $year;
        $this->month = $month;
        $this->kpis = $kpis;
    }

    public function collection()
    {
        return collect([
            [
                'GOP %',
                number_format($this->kpis['gop_percentage'], 2) . '%',
                'Gross Operating Profit as % of Revenue'
            ],
            [
                'Labor Cost %',
                number_format($this->kpis['labor_cost_percentage'], 2) . '%',
                'Total Payroll as % of Revenue'
            ],
            [
                'Labor Cost',
                'Rp ' . number_format($this->kpis['labor_cost'], 0, ',', '.'),
                'Total Payroll Expenses'
            ],
            [
                'F&B Cost %',
                number_format($this->kpis['fnb_cost_percentage'], 2) . '%',
                'F&B Cost as % of F&B Revenue'
            ],
            [
                'Expense per Available Room',
                'Rp ' . number_format($this->kpis['expense_per_available_room'], 0, ',', '.'),
                'Total Expense / Available Rooms'
            ],
            [
                'Revenue per Available Room',
                'Rp ' . number_format($this->kpis['revenue_per_available_room'], 0, ',', '.'),
                'Total Revenue / Available Rooms'
            ],
        ]);
    }

    public function headings(): array
    {
        return ['KPI', 'Value', 'Description'];
    }

    public function title(): string
    {
        return 'KPIs';
    }
}

// Comparative Sheet
class ComparativeSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $propertyName;
    protected $year;
    protected $month;
    protected $comparative;

    public function __construct(string $propertyName, int $year, int $month, array $comparative)
    {
        $this->propertyName = $propertyName;
        $this->year = $year;
        $this->month = $month;
        $this->comparative = $comparative;
    }

    public function collection()
    {
        return collect([
            [
                'Metric',
                'Current (' . $this->comparative['current']['period'] . ')',
                'Previous Month (' . $this->comparative['mom']['period'] . ')',
                'MoM Change %',
                'Last Year (' . $this->comparative['yoy']['period'] . ')',
                'YoY Change %'
            ],
            [
                'Revenue',
                $this->comparative['current']['revenue'],
                $this->comparative['mom']['revenue'],
                number_format($this->comparative['mom']['revenue_change'], 2) . '%',
                $this->comparative['yoy']['revenue'],
                number_format($this->comparative['yoy']['revenue_change'], 2) . '%',
            ],
            [
                'Expense',
                $this->comparative['current']['expense'],
                $this->comparative['mom']['expense'],
                number_format($this->comparative['mom']['expense_change'], 2) . '%',
                $this->comparative['yoy']['expense'],
                number_format($this->comparative['yoy']['expense_change'], 2) . '%',
            ],
            [
                'GOP',
                $this->comparative['current']['gop'],
                $this->comparative['mom']['gop'],
                number_format($this->comparative['mom']['gop_change'], 2) . '%',
                $this->comparative['yoy']['gop'],
                number_format($this->comparative['yoy']['gop_change'], 2) . '%',
            ],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Comparative Analysis';
    }
}
