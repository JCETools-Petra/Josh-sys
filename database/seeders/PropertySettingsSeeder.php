<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\PropertySetting;

class PropertySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all properties
        $properties = Property::all();

        // Default settings configuration
        $defaultSettings = [
            // Financial Settings
            [
                'key' => 'tax_rate',
                'value' => '0.10',
                'type' => 'decimal',
                'category' => 'financial',
                'description' => 'Tax rate (default 10%)',
            ],
            [
                'key' => 'service_charge_rate',
                'value' => '0.05',
                'type' => 'decimal',
                'category' => 'financial',
                'description' => 'Service charge rate (default 5%)',
            ],
            [
                'key' => 'breakfast_rate',
                'value' => '50000',
                'type' => 'decimal',
                'category' => 'financial',
                'description' => 'Breakfast rate per person per day',
            ],
            [
                'key' => 'payment_tolerance',
                'value' => '100',
                'type' => 'decimal',
                'category' => 'financial',
                'description' => 'Payment tolerance amount in smallest currency unit (e.g., 100 = Rp 100)',
            ],

            // Operational Settings
            [
                'key' => 'allow_overbooking',
                'value' => '0',
                'type' => 'boolean',
                'category' => 'operational',
                'description' => 'Allow overbooking of rooms',
            ],
            [
                'key' => 'max_check_in_days_advance',
                'value' => '365',
                'type' => 'integer',
                'category' => 'operational',
                'description' => 'Maximum days in advance for check-in',
            ],
            [
                'key' => 'auto_cancel_reservation_hours',
                'value' => '24',
                'type' => 'integer',
                'category' => 'operational',
                'description' => 'Auto-cancel reservation if not checked in within hours',
            ],
            [
                'key' => 'default_check_in_time',
                'value' => '14:00',
                'type' => 'string',
                'category' => 'operational',
                'description' => 'Default check-in time',
            ],
            [
                'key' => 'default_check_out_time',
                'value' => '12:00',
                'type' => 'string',
                'category' => 'operational',
                'description' => 'Default check-out time',
            ],

            // Validation Settings
            [
                'key' => 'require_guest_id_validation',
                'value' => '1',
                'type' => 'boolean',
                'category' => 'validation',
                'description' => 'Require strict guest ID validation',
            ],
            [
                'key' => 'require_payment_full_amount',
                'value' => '1',
                'type' => 'boolean',
                'category' => 'validation',
                'description' => 'Require full payment before checkout',
            ],
        ];

        // Seed settings for each property
        foreach ($properties as $property) {
            foreach ($defaultSettings as $setting) {
                PropertySetting::updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'key' => $setting['key'],
                    ],
                    [
                        'value' => $setting['value'],
                        'type' => $setting['type'],
                        'category' => $setting['category'],
                        'description' => $setting['description'],
                    ]
                );
            }

            $this->command->info("Seeded settings for property: {$property->name}");
        }

        $this->command->info('Property settings seeded successfully!');
    }
}
