<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\FinancialReportService;
use App\Http\Requests\StoreFinancialEntryRequest;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\ImportBudgetRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialController extends Controller
{
    protected $financialService;

    public function __construct(FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Show property selection page for admin.
     */
    public function selectProperty()
    {
        $properties = Property::orderBy('name')->get();
        return view('admin.financial.select-property', compact('properties'));
    }

    /**
     * Show the actual input form for monthly expenses (Admin version).
     */
    public function showInputActual(Request $request, Property $property)
    {
        // Get current month and year or from request
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        // Get categories grouped by department
        $departments = $this->financialService->getCategoriesForInput($property->id);

        // Get existing entries for this month to pre-fill the form
        $existingEntries = \App\Models\FinancialEntry::where('property_id', $property->id)
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->keyBy('financial_category_id');

        return view('admin.financial.input-actual', compact(
            'property',
            'year',
            'month',
            'departments',
            'existingEntries'
        ));
    }

    /**
     * Store the actual monthly expenses (Admin version).
     */
    public function storeInputActual(StoreFinancialEntryRequest $request, Property $property)
    {
        $validated = $request->validated();

        try {
            \DB::beginTransaction();

            // Save or update each entry
            foreach ($validated['entries'] as $entry) {
                $this->financialService->saveEntry(
                    $property->id,
                    $entry['category_id'],
                    $validated['year'],
                    $validated['month'],
                    $entry['actual_value'],
                    // Jika budget juga diinput di form actual, kirim nilainya, jika tidak kirim null
                    $entry['budget_value'] ?? null
                );
            }

            \DB::commit();

            return redirect()->route('admin.financial.input-actual', [
                'property' => $property->id,
                'year' => $validated['year'],
                'month' => $validated['month']
            ])->with('success', 'Data berhasil disimpan untuk ' . $property->name);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to save financial entries', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Show the P&L report (Admin version).
     */
    public function showReport(Request $request, Property $property)
    {
        // Get current month and year or from request
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        // Get P&L data
        $pnlData = $this->financialService->getPnL($property->id, $year, $month);

        // Get additional data for enhanced features
        $chartData = $this->financialService->getChartData($property->id, $year, $month);
        $kpis = $this->financialService->getKPIs($property->id, $year, $month);
        $comparative = $this->financialService->getComparativeAnalysis($property->id, $year, $month);
        $alerts = $this->financialService->getBudgetAlerts($property->id, $year, $month);
        $forecast = $this->financialService->getForecast($property->id, $year, $month);

        // Generate month options for dropdown
        $months = collect(range(1, 12))->map(function ($m) {
            return [
                'value' => $m,
                'name' => Carbon::create(2000, $m, 1)->format('F')
            ];
        });

        // Generate year options (current year ± 2 years)
        $currentYear = Carbon::now()->year;
        $years = range($currentYear - 2, $currentYear + 2);

        return view('admin.financial.report', compact(
            'property',
            'year',
            'month',
            'pnlData',
            'chartData',
            'kpis',
            'comparative',
            'alerts',
            'forecast',
            'months',
            'years'
        ));
    }

    /**
     * Export P&L report to Excel (Admin version).
     */
    public function exportExcel(Request $request, Property $property)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $fileName = 'PnL_' . $property->name . '_' . Carbon::create($year, $month, 1)->format('Y-m') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PnLExport($property->id, $property->name, $year, $month),
            $fileName
        );
    }

    /**
     * Export P&L report to PDF (Admin version).
     */
    public function exportPdf(Request $request, Property $property)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $pnlData = $this->financialService->getPnL($property->id, $year, $month);
        $kpis = $this->financialService->getKPIs($property->id, $year, $month);
        $comparative = $this->financialService->getComparativeAnalysis($property->id, $year, $month);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('financial.pdf.pnl-report', compact(
            'property',
            'year',
            'month',
            'pnlData',
            'kpis',
            'comparative'
        ));

        $fileName = 'PnL_' . $property->name . '_' . Carbon::create($year, $month, 1)->format('Y-m') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Show financial dashboard (Admin version).
     */
    public function showDashboard(Request $request, Property $property)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $dashboardData = $this->financialService->getDashboardSummary($property->id, $year, $month);
        $chartData = $this->financialService->getChartData($property->id, $year, $month);
        $alerts = $this->financialService->getBudgetAlerts($property->id, $year, $month);

        return view('financial.dashboard', compact(
            'property',
            'year',
            'month',
            'dashboardData',
            'chartData',
            'alerts'
        ));
    }

    /**
     * Copy data from previous month (Admin version).
     */
    public function copyFromPreviousMonth(Request $request, Property $property)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:' . config('hotelier.validation.year_range.min') . '|max:' . config('hotelier.validation.year_range.max'),
            'month' => 'required|integer|min:' . config('hotelier.validation.month_range.min') . '|max:' . config('hotelier.validation.month_range.max'),
        ]);

        try {
            \DB::beginTransaction();

            // Get previous month
            $date = Carbon::create($validated['year'], $validated['month'], 1);
            $prevMonth = $date->copy()->subMonth();

            // Get all entries from previous month
            $prevEntries = \App\Models\FinancialEntry::where('property_id', $property->id)
                ->where('year', $prevMonth->year)
                ->where('month', $prevMonth->month)
                ->get();

            // Copy to current month
            $copiedCount = 0;
            foreach ($prevEntries as $entry) {
                $this->financialService->saveEntry(
                    $property->id,
                    $entry->financial_category_id,
                    $validated['year'],
                    $validated['month'],
                    $entry->actual_value,
                    $entry->budget_value
                );
                $copiedCount++;
            }

            \DB::commit();

            return redirect()->route('admin.financial.input-actual', [
                'property' => $property->id,
                'year' => $validated['year'],
                'month' => $validated['month']
            ])->with('success', "Berhasil menyalin $copiedCount data dari bulan sebelumnya untuk {$property->name}.");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to copy financial entries', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyalin data. Silakan coba lagi.');
        }
    }

    /**
     * Show budget input form for annual budget (Admin version).
     */
    public function showInputBudget(Request $request, Property $property)
    {
        // Get year from request or use next year
        $year = $request->input('year', Carbon::now()->addYear()->year);

        // Get categories grouped by department
        $departments = $this->financialService->getCategoriesForInput($property->id);

        // Get existing budget entries for this year
        $existingEntries = \App\Models\FinancialEntry::where('property_id', $property->id)
            ->where('year', $year)
            ->get()
            ->groupBy('financial_category_id')
            ->map(function ($entries) {
                // Sum all budget values for the year
                return $entries->sum('budget_value');
            });

        return view('admin.financial.input-budget', compact(
            'property',
            'year',
            'departments',
            'existingEntries'
        ));
    }

    /**
     * Store the annual budget (Admin version).
     */
    public function storeInputBudget(StoreBudgetRequest $request, Property $property)
    {
        $validated = $request->validated();

        $mode = $validated['mode'] ?? 'replace'; // Default to replace mode
        $updatedCount = 0;
        $skippedCount = 0;

        try {
            \DB::beginTransaction();

            // Distribute annual budget across all 12 months
            foreach ($validated['entries'] as $entry) {
                $monthlyBudget = $entry['budget_value'] / 12;

                // Check if data already exists for this category/year
                $existingCount = \App\Models\FinancialEntry::where('property_id', $property->id)
                    ->where('financial_category_id', $entry['category_id'])
                    ->where('year', $validated['year'])
                    ->count();

                if ($existingCount > 0 && $mode === 'update') {
                    // Skip if mode is 'update' and data exists (prevent overwrite)
                    \Log::warning("Skipped budget update for category {$entry['category_id']} - data already exists", [
                        'property_id' => $property->id,
                        'year' => $validated['year'],
                        'mode' => $mode,
                        'existing_entries' => $existingCount
                    ]);
                    $skippedCount++;
                    continue;
                }

                // Log the operation for audit
                \Log::info("Saving budget for category {$entry['category_id']}", [
                    'property_id' => $property->id,
                    'category_id' => $entry['category_id'],
                    'year' => $validated['year'],
                    'yearly_total' => $entry['budget_value'],
                    'monthly_value' => $monthlyBudget,
                    'mode' => $mode,
                    'existing_entries' => $existingCount,
                ]);

                for ($month = 1; $month <= 12; $month++) {
                    // PERBAIKAN: Gunakan null untuk actual_value agar tidak menimpa data yang sudah ada
                    $this->financialService->saveEntry(
                        $property->id,
                        $entry['category_id'],
                        $validated['year'],
                        $month,
                        null, // Passing NULL menjaga actual_value yang sudah ada tetap aman
                        $monthlyBudget
                    );
                }
                $updatedCount++;
            }

            \DB::commit();

            $message = "Budget tahunan berhasil disimpan untuk {$property->name}. ";
            $message .= "Updated: {$updatedCount} kategori";

            if ($skippedCount > 0) {
                $message .= ", Skipped: {$skippedCount} kategori (data already exists)";
            }

            return redirect()->route('admin.financial.input-budget', [
                'property' => $property->id,
                'year' => $validated['year']
            ])->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to save budget entries', [
                'property_id' => $property->id,
                'year' => $validated['year'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan budget. Silakan coba lagi.');
        }
    }

    /**
     * Download budget template for annual budget input (Admin version).
     */
    public function downloadBudgetTemplate(Request $request, Property $property)
    {
        $year = $request->input('year', Carbon::now()->addYear()->year);
        $fileName = 'Budget_Template_' . $property->name . '_' . $year . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BudgetTemplateExport($property->id, $year),
            $fileName
        );
    }

    /**
     * Import budget from uploaded template (Admin version).
     */
    public function importBudgetTemplate(ImportBudgetRequest $request, Property $property)
    {
        $validated = $request->validated();

        try {
            $import = new \App\Imports\BudgetTemplateImport($property->id, $validated['year']);

            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();

            // Check if no data was imported
            if ($importedCount == 0 && count($errors) == 0) {
                $message = 'Tidak ada data yang berhasil diimport.';
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} baris dilewati karena semua nilai bulan kosong/nol.";
                }
                $message .= ' Pastikan file Excel berisi data budget yang valid. Cek log di storage/logs/laravel.log untuk detail.';

                return redirect()->route('admin.financial.input-budget', [
                    'property' => $property->id,
                    'year' => $validated['year']
                ])->with('error', $message);
            }

            if (count($errors) > 0) {
                // Format errors as HTML list for better readability
                $errorList = '<ul class="list-disc ml-5">';
                foreach ($errors as $error) {
                    $errorList .= '<li>' . $error . '</li>';
                }
                $errorList .= '</ul>';

                $months = $importedCount / 12; // Each category = 12 months
                $message = $importedCount > 0
                    ? "Import selesai: {$months} kategori ({$importedCount} bulan) berhasil diimport."
                    : "Import gagal - tidak ada data yang berhasil diimport.";

                return redirect()->route('admin.financial.input-budget', [
                    'property' => $property->id,
                    'year' => $validated['year']
                ])->with('warning', $message . " Error yang terjadi: " . $errorList);
            }

            $months = $importedCount / 12; // Each category = 12 months
            $message = "Berhasil mengimport budget untuk {$months} kategori ({$importedCount} bulan) di " . $property->name;

            if ($skippedCount > 0) {
                $message .= ". {$skippedCount} baris dilewati karena tidak memiliki data budget (semua nilai kosong/nol).";
            }

            return redirect()->route('admin.financial.input-budget', [
                'property' => $property->id,
                'year' => $validated['year']
            ])->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.financial.input-budget', [
                'property' => $property->id,
                'year' => $validated['year']
            ])->with('error', 'Gagal mengimport file: ' . $e->getMessage());
        }
    }
}
