<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\Request;

class BarDisplayController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua properti untuk filter dropdown
        $properties = Property::orderBy('name')->get();

        $selectedPropertyId = $request->input('property_id');
        $selectedProperty = null;
        $roomTypePrices = collect();

        if ($selectedPropertyId) {
            // Jika ada properti yang dipilih, ambil datanya
            $selectedProperty = Property::find($selectedPropertyId);

            // ✅ STABILITY FIX: Validate property exists before proceeding
            if (!$selectedProperty) {
                return redirect()->back()->with('error', 'Property tidak ditemukan.');
            }

            // Ambil semua tipe kamar untuk properti ini, beserta aturan harganya
            $roomTypes = RoomType::where('property_id', $selectedPropertyId)
                ->with('pricingRule')
                ->get();

            // Siapkan data untuk ditampilkan di tabel
            $roomTypePrices = $roomTypes->map(function ($roomType) {
                $prices = [];
                // Ambil 5 level BAR dari pricing rule
                if ($roomType->pricingRule) {
                    $prices = [
                        $roomType->pricingRule->bar_1,
                        $roomType->pricingRule->bar_2,
                        $roomType->pricingRule->bar_3,
                        $roomType->pricingRule->bar_4,
                        $roomType->pricingRule->bar_5,
                    ];
                }
                return [
                    'name' => $roomType->name,
                    'prices' => $prices,
                ];
            });
        }

        return view('admin.bar_prices.index', compact('properties', 'selectedProperty', 'roomTypePrices', 'selectedPropertyId'));
    }
}