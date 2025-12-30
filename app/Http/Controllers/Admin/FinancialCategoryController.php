<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialCategory;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinancialCategoryController extends Controller
{
    /**
     * Display a listing of financial categories.
     */
    public function index(Request $request)
    {
        $propertyId = $request->input('property_id');

        $properties = Property::orderBy('name')->get();

        $query = FinancialCategory::with(['parent', 'children'])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        $categories = $query->get();

        // Organize into tree structure
        $rootCategories = $categories->whereNull('parent_id');

        return view('admin.financial-categories.index', compact('categories', 'rootCategories', 'properties', 'propertyId'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(Request $request)
    {
        $properties = Property::orderBy('name')->get();
        $propertyId = $request->input('property_id');

        // Get potential parent categories for the selected property (hierarchically ordered)
        $parentCategories = [];
        if ($propertyId) {
            $parentCategories = FinancialCategory::getHierarchicalListForDropdown((int) $propertyId);
        }

        return view('admin.financial-categories.create', compact('properties', 'propertyId', 'parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'parent_id' => 'nullable|exists:financial_categories,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:revenue,expense',
            'is_payroll' => 'boolean',
            'allows_manual_input' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Check for duplicate code in same property
        $exists = FinancialCategory::where('property_id', $validated['property_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['code' => 'Kode kategori sudah digunakan untuk properti ini.'])
                ->withInput();
        }

        // Validate parent belongs to same property
        if (!empty($validated['parent_id'])) {
            $parent = FinancialCategory::find($validated['parent_id']);
            if ($parent->property_id != $validated['property_id']) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Parent kategori harus dari properti yang sama.'])
                    ->withInput();
            }
        }

        $validated['is_payroll'] = $request->boolean('is_payroll');
        $validated['allows_manual_input'] = $request->boolean('allows_manual_input');

        FinancialCategory::create($validated);

        return redirect()->route('admin.financial-categories.index', ['property_id' => $validated['property_id']])
            ->with('success', 'Kategori finansial berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(FinancialCategory $financialCategory)
    {
        $properties = Property::orderBy('name')->get();

        // Get potential parent categories (excluding self and descendants, hierarchically ordered)
        $parentCategories = FinancialCategory::getHierarchicalListForDropdown(
            $financialCategory->property_id,
            $financialCategory->id
        );

        return view('admin.financial-categories.edit', compact('financialCategory', 'properties', 'parentCategories'));
    }

    /**
     * Update the category.
     */
    public function update(Request $request, FinancialCategory $financialCategory)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'parent_id' => 'nullable|exists:financial_categories,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:revenue,expense',
            'is_payroll' => 'boolean',
            'allows_manual_input' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Check for duplicate code in same property (excluding current record)
        $exists = FinancialCategory::where('property_id', $validated['property_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $financialCategory->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['code' => 'Kode kategori sudah digunakan untuk properti ini.'])
                ->withInput();
        }

        // Validate parent (can't be self or descendant)
        if (!empty($validated['parent_id'])) {
            if ($validated['parent_id'] == $financialCategory->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Kategori tidak bisa menjadi parent dari dirinya sendiri.'])
                    ->withInput();
            }

            $parent = FinancialCategory::find($validated['parent_id']);
            if ($parent->property_id != $validated['property_id']) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Parent kategori harus dari properti yang sama.'])
                    ->withInput();
            }
        }

        $validated['is_payroll'] = $request->boolean('is_payroll');
        $validated['allows_manual_input'] = $request->boolean('allows_manual_input');

        $financialCategory->update($validated);

        return redirect()->route('admin.financial-categories.index', ['property_id' => $validated['property_id']])
            ->with('success', 'Kategori finansial berhasil diupdate.');
    }

    /**
     * Remove the category.
     */
    public function destroy(FinancialCategory $financialCategory)
    {
        // Check if category has children
        if ($financialCategory->children()->count() > 0) {
            return redirect()->back()
                ->withErrors(['delete' => 'Tidak bisa menghapus kategori yang memiliki sub-kategori. Hapus sub-kategori terlebih dahulu.']);
        }

        // Check if category has financial entries
        if ($financialCategory->entries()->count() > 0) {
            return redirect()->back()
                ->withErrors(['delete' => 'Tidak bisa menghapus kategori yang sudah memiliki data transaksi.']);
        }

        $propertyId = $financialCategory->property_id;
        $financialCategory->delete();

        return redirect()->route('admin.financial-categories.index', ['property_id' => $propertyId])
            ->with('success', 'Kategori finansial berhasil dihapus.');
    }
}
