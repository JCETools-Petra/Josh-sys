<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\Request;

class PricingRuleController extends Controller
{
    public function index(Property $property)
    {
        $property->load('roomTypes.pricingRule');
        return view('admin.pricing_rules.edit', compact('property'));
    }

    /**
     * Menyimpan tipe kamar baru.
     */
    public function storeRoomType(Request $request, Property $property)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $roomType = $property->roomTypes()->create(['name' => $request->name]);

        // =======================================================
        // !! PERUBAHAN UTAMA ADA DI SINI !!
        // Kita berikan nilai default saat membuat pricing rule baru.
        // =======================================================
        $roomType->pricingRule()->create([
            'publish_rate' => 0,
            'bottom_rate' => 0,
            'percentage_increase' => 0,
            'starting_bar' => 1,
        ]);

        return redirect()->back()->with('success', 'Tipe kamar baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui aturan harga untuk tipe kamar tertentu.
     */
    public function updatePricingRule(Request $request, Property $property, RoomType $roomType)
    {
        $validated = $request->validate([
            'publish_rate' => 'required|numeric|min:0',
            'bottom_rate' => 'required|numeric|min:0|lte:publish_rate',
            'percentage_increase' => 'required|numeric|min:0|max:100',
            'starting_bar' => 'required|integer|in:1,2,3,4,5',
        ]);

        // Hitung nilai BAR secara otomatis
        $barPrices = $this->calculateBarPrices(
            $validated['bottom_rate'],
            $validated['percentage_increase'],
            $validated['starting_bar']
        );

        // Gabungkan dengan data yang sudah divalidasi
        $validated = array_merge($validated, $barPrices);

        $roomType->pricingRule()->update($validated);

        return redirect()->back()->with('success', "Harga untuk tipe kamar '{$roomType->name}' berhasil diperbarui.");
    }

    /**
     * Menghitung harga untuk setiap level BAR (1-5)
     *
     * @param float $bottomRate
     * @param float $percentageIncrease
     * @param int $startingBar
     * @return array
     */
    private function calculateBarPrices(float $bottomRate, float $percentageIncrease, int $startingBar): array
    {
        $barPrices = [];
        $increaseFactor = 1 + ($percentageIncrease / 100);

        for ($bar = 1; $bar <= 5; $bar++) {
            if ($bar < $startingBar) {
                // Sebelum starting bar, gunakan bottom rate
                $barPrices["bar_{$bar}"] = $bottomRate;
            } else {
                // Hitung kenaikan dari starting bar
                $stepsFromStart = $bar - $startingBar;
                $price = $bottomRate;

                for ($step = 0; $step < $stepsFromStart; $step++) {
                    $price *= $increaseFactor;
                }

                $barPrices["bar_{$bar}"] = round($price, 2);
            }
        }

        return $barPrices;
    }

    /**
     * Memperbarui setelan Kapasitas Bar untuk seluruh properti.
     */
    public function updatePropertyBars(Request $request, Property $property)
    {
        $validated = $request->validate([
            'bar_1' => 'required|integer|min:0',
            'bar_2' => 'required|integer|min:0|gte:bar_1',
            'bar_3' => 'required|integer|min:0|gte:bar_2',
            'bar_4' => 'required|integer|min:0|gte:bar_3',
            'bar_5' => 'required|integer|min:0|gte:bar_4',
        ]);

        $property->update($validated);

        return redirect()->back()->with('success', 'Kapasitas Bar untuk properti berhasil diperbarui.');
    }

    /**
     * Menghapus tipe kamar.
     */
    public function destroyRoomType(Property $property, RoomType $roomType)
    {
        $roomType->delete();
        return redirect()->back()->with('success', "Tipe kamar '{$roomType->name}' berhasil dihapus.");
    }
}