<?php

namespace App\Exports;

// [PERBAIKAN] Pastikan namespace sheet ini benar
use App\Exports\Sheets\KpiAnalysisMonthlySheet; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Property;
use Carbon\CarbonPeriod;

class KpiAnalysisExport implements WithMultipleSheets
{
    use Exportable;

    protected $kpiData;
    protected $dailyData;
    protected $selectedProperty;
    protected $filteredIncomes;
    protected $miceBookings;
    protected $startDate; // <-- TAMBAHKAN INI
    protected $endDate;   // <-- TAMBAHKAN INI

    // ==========================================================
    // >> PERUBAHAN DI CONSTRUCTOR <<
    // Tambahkan $startDate dan $endDate
    // ==========================================================
    public function __construct(
        $kpiData, 
        Collection $dailyData, 
        ?Property $selectedProperty, 
        Collection $filteredIncomes, 
        Collection $miceBookings,
        string $startDate, // <-- TAMBAHKAN INI
        string $endDate   // <-- TAMBAHKAN INI
    ) {
        $this->kpiData = $kpiData;
        $this->dailyData = $dailyData;
        $this->selectedProperty = $selectedProperty;
        $this->filteredIncomes = $filteredIncomes;
        $this->miceBookings = $miceBookings;
        $this->startDate = $startDate; // <-- TAMBAHKAN INI
        $this->endDate = $endDate;     // <-- TAMBAHKAN INI
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // ==========================================================
        // >> AWAL PERUBAHAN <<
        //
        // Logika looping per bulan dihapus. Kita sekarang berasumsi
        // bahwa $kpiData dan $dailyData yang diterima dari DashboardController
        // sudah mencakup seluruh rentang tanggal yang diinginkan.
        //
        // ==========================================================

        // 1. Tentukan Judul Sheet (Bulan)
        $monthName = "Analisis KPI"; // Default
        if ($this->dailyData->isNotEmpty()) {
            // Coba dapatkan rentang tanggal dari data harian
            try {
                // Gunakan tanggal dari controller, bukan dari $dailyData
                $firstDate = Carbon::parse($this->startDate);
                $lastDate = Carbon::parse($this->endDate);
                
                if ($firstDate->format('Y-m') === $lastDate->format('Y-m')) {
                    $monthName = $firstDate->isoFormat('MMMM YYYY');
                } else {
                    $monthName = $firstDate->isoFormat('MMM YYYY') . ' - ' . $lastDate->isoFormat('MMM YYYY');
                }
            } catch (\Exception $e) {
                // Gunakan nama default jika format tanggal tidak sesuai
                $monthName = "Analisis KPI";
            }
        }

        // ==========================================================
        // >> PERUBAHAN DI SINI <<
        // Teruskan $startDate dan $endDate ke KpiAnalysisMonthlySheet
        // ==========================================================
        $sheets[] = new \App\Exports\Sheets\KpiAnalysisMonthlySheet(
            $monthName,
            $this->dailyData,       // Data harian yang sudah benar
            $this->kpiData,         // Data KPI yang sudah benar
            $this->selectedProperty,
            $this->miceBookings,    // Data MICE
            $this->startDate,       // <-- TAMBAHKAN INI
            $this->endDate          // <-- TAMBAHKAN INI
        );
        
        // ==========================================================
        // >> AKHIR PERUBAHAN <<
        // ==========================================================

        return $sheets;
    }
}