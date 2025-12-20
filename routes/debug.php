<?php

use Illuminate\Support\Facades\Route;
use App\Models\FinancialEntry;
use App\Models\FinancialCategory;

Route::get('/debug/budget/{propertyId}/{year}/{categoryId?}', function ($propertyId, $year, $categoryId = null) {
    $query = FinancialEntry::where('property_id', $propertyId)
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
            'budget_values' => $catEntries->pluck('budget_value')->toArray(),
            'sum_budget' => $catEntries->sum('budget_value'),
            'sum_actual' => $catEntries->sum('actual_value'),
            'sum_forecast' => $catEntries->sum('forecast_value'),
        ];
    }

    return response()->json([
        'property_id' => $propertyId,
        'year' => $year,
        'total_entries' => $entries->count(),
        'categories' => $results,
    ], 200, [], JSON_PRETTY_PRINT);
});
