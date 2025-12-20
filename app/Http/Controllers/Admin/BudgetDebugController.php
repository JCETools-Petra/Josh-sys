<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialEntry;
use App\Models\FinancialCategory;
use App\Models\Property;
use Illuminate\Http\Request;

class BudgetDebugController extends Controller
{
    /**
     * Show budget verification interface
     */
    public function verify(Request $request, Property $property)
    {
        $year = $request->input('year', now()->year);

        $entries = FinancialEntry::where('property_id', $property->id)
            ->where('year', $year)
            ->orderBy('financial_category_id')
            ->orderBy('month')
            ->get();

        $grouped = $entries->groupBy('financial_category_id');

        $stats = [];
        $issues = [];

        foreach ($grouped as $catId => $catEntries) {
            $category = FinancialCategory::find($catId);
            if (!$category) continue;

            $monthCount = $catEntries->count();
            $budgetSum = $catEntries->sum('budget_value');
            $actualSum = $catEntries->sum('actual_value');
            $forecastSum = $catEntries->sum('forecast_value');

            $avgBudget = $monthCount > 0 ? $budgetSum / $monthCount : 0;

            $stats[] = [
                'category_id' => $catId,
                'category_name' => $category->name,
                'category_path' => $category->getFullPath(),
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
                    'category_name' => $category->name,
                    'message' => "Expected 12 months, found {$monthCount}",
                    'severity' => 'error',
                ];
            }

            $months = $catEntries->pluck('month')->toArray();
            $duplicates = array_diff_assoc($months, array_unique($months));
            if (count($duplicates) > 0) {
                $issues[] = [
                    'type' => 'DUPLICATE_MONTHS',
                    'category_id' => $catId,
                    'category_name' => $category->name,
                    'message' => "Duplicate months found",
                    'severity' => 'error',
                ];
            }
        }

        return view('admin.financial.debug-verify', compact(
            'property',
            'year',
            'stats',
            'issues'
        ));
    }

    /**
     * Show detailed budget data for specific category
     */
    public function show(Request $request, Property $property)
    {
        $year = $request->input('year', now()->year);
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return redirect()->route('admin.financial.debug.verify', $property->id)
                ->with('error', 'Please specify a category_id');
        }

        $category = FinancialCategory::find($categoryId);
        if (!$category) {
            return redirect()->route('admin.financial.debug.verify', $property->id)
                ->with('error', "Category ID {$categoryId} not found");
        }

        $entries = FinancialEntry::where('property_id', $property->id)
            ->where('financial_category_id', $categoryId)
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        $totalBudget = $entries->sum('budget_value');
        $totalActual = $entries->sum('actual_value');
        $totalForecast = $entries->sum('forecast_value');

        $avgBudget = $entries->count() > 0 ? $totalBudget / $entries->count() : 0;
        $avgActual = $entries->count() > 0 ? $totalActual / $entries->count() : 0;
        $avgForecast = $entries->count() > 0 ? $totalForecast / $entries->count() : 0;

        return view('admin.financial.debug-show', compact(
            'property',
            'category',
            'year',
            'entries',
            'totalBudget',
            'totalActual',
            'totalForecast',
            'avgBudget',
            'avgActual',
            'avgForecast'
        ));
    }

    /**
     * API endpoint for getting budget data as JSON
     */
    public function api(Request $request, Property $property)
    {
        $year = $request->input('year', now()->year);
        $categoryId = $request->input('category_id');

        $query = FinancialEntry::where('property_id', $property->id)
            ->where('year', $year);

        if ($categoryId) {
            $query->where('financial_category_id', $categoryId);
        }

        $entries = $query->orderBy('financial_category_id')
            ->orderBy('month')
            ->get();

        $grouped = $entries->groupBy('financial_category_id');

        $results = [];
        foreach ($grouped as $catId => $catEntries) {
            $category = FinancialCategory::find($catId);
            $results[] = [
                'category_id' => $catId,
                'category_name' => $category ? $category->name : 'Unknown',
                'category_path' => $category ? $category->getFullPath() : 'Unknown',
                'entries_count' => $catEntries->count(),
                'months' => $catEntries->pluck('month')->toArray(),
                'budget_values' => $catEntries->pluck('budget_value')->map(function($v) {
                    return (float) $v;
                })->toArray(),
                'sum_budget' => (float) $catEntries->sum('budget_value'),
                'sum_actual' => (float) $catEntries->sum('actual_value'),
                'sum_forecast' => (float) $catEntries->sum('forecast_value'),
            ];
        }

        return response()->json([
            'property_id' => $property->id,
            'property_name' => $property->name,
            'year' => $year,
            'total_entries' => $entries->count(),
            'categories' => $results,
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
