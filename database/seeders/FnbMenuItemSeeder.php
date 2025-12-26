<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FnbMenuItem;
use App\Models\Property;

class FnbMenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first property (you can adjust this based on your needs)
        $property = Property::first();

        if (!$property) {
            $this->command->error('No property found. Please create a property first.');
            return;
        }

        $menuItems = [
            // Breakfast
            [
                'property_id' => $property->id,
                'name' => 'Nasi Goreng',
                'category' => 'breakfast',
                'price' => 35000,
                'description' => 'Nasi goreng spesial dengan telur mata sapi',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Mie Goreng',
                'category' => 'breakfast',
                'price' => 30000,
                'description' => 'Mie goreng spesial dengan sayuran',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'American Breakfast',
                'category' => 'breakfast',
                'price' => 55000,
                'description' => 'Sosis, bacon, telur, roti panggang, dan hash brown',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Nasi Uduk',
                'category' => 'breakfast',
                'price' => 25000,
                'description' => 'Nasi uduk dengan lauk lengkap',
                'is_available' => true,
            ],

            // Lunch & Dinner
            [
                'property_id' => $property->id,
                'name' => 'Nasi Ayam Penyet',
                'category' => 'lunch',
                'price' => 40000,
                'description' => 'Ayam goreng penyet dengan sambal terasi',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Soto Ayam',
                'category' => 'lunch',
                'price' => 30000,
                'description' => 'Soto ayam kuning khas Jawa',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Nasi Rendang',
                'category' => 'dinner',
                'price' => 45000,
                'description' => 'Rendang daging sapi dengan nasi putih',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Gado-Gado',
                'category' => 'lunch',
                'price' => 28000,
                'description' => 'Sayuran rebus dengan bumbu kacang',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Capcay',
                'category' => 'dinner',
                'price' => 35000,
                'description' => 'Tumisan sayuran campur ala Chinese',
                'is_available' => true,
            ],

            // Beverages
            [
                'property_id' => $property->id,
                'name' => 'Kopi Hitam',
                'category' => 'beverage',
                'price' => 15000,
                'description' => 'Kopi hitam pilihan',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Kopi Susu',
                'category' => 'beverage',
                'price' => 18000,
                'description' => 'Kopi dengan susu',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Teh Manis',
                'category' => 'beverage',
                'price' => 10000,
                'description' => 'Teh manis hangat/dingin',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Jus Jeruk',
                'category' => 'beverage',
                'price' => 20000,
                'description' => 'Jus jeruk segar',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Jus Alpukat',
                'category' => 'beverage',
                'price' => 22000,
                'description' => 'Jus alpukat segar',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Es Teh Manis',
                'category' => 'beverage',
                'price' => 12000,
                'description' => 'Es teh manis segar',
                'is_available' => true,
            ],

            // Snacks
            [
                'property_id' => $property->id,
                'name' => 'French Fries',
                'category' => 'snack',
                'price' => 25000,
                'description' => 'Kentang goreng dengan saus',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Chicken Wings',
                'category' => 'snack',
                'price' => 35000,
                'description' => 'Sayap ayam goreng pedas',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Pisang Goreng',
                'category' => 'snack',
                'price' => 15000,
                'description' => 'Pisang goreng krispy',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Tahu Isi',
                'category' => 'snack',
                'price' => 18000,
                'description' => 'Tahu isi sayuran dan daging',
                'is_available' => true,
            ],

            // Tambahan
            [
                'property_id' => $property->id,
                'name' => 'Nasi Putih',
                'category' => 'lunch',
                'price' => 8000,
                'description' => 'Nasi putih porsi 1 piring',
                'is_available' => true,
            ],
            [
                'property_id' => $property->id,
                'name' => 'Air Mineral',
                'category' => 'beverage',
                'price' => 8000,
                'description' => 'Air mineral botol 600ml',
                'is_available' => true,
            ],
        ];

        foreach ($menuItems as $item) {
            FnbMenuItem::create($item);
        }

        $this->command->info('Menu items seeded successfully!');
    }
}
