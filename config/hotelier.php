<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Property-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration handles special business logic for specific properties.
    | Instead of hard-coding property names in controllers, define them here.
    |
    */

    'special_properties' => [
        // Sunnyday Inn receives breakfast revenue from source properties
        'breakfast_recipient' => [
            'name' => env('BREAKFAST_RECIPIENT_PROPERTY', 'Sunnyday Inn'),
            'sources' => [
                env('BREAKFAST_SOURCE_1', 'Hotel Akat'),
                env('BREAKFAST_SOURCE_2', 'Hotel Ermasu'),
                env('BREAKFAST_SOURCE_3', 'Bell Hotel'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Financial Report Constants
    |--------------------------------------------------------------------------
    |
    | Constants used in financial calculations and reporting
    |
    */

    'financial' => [
        'budget_variance_threshold' => 10, // Percentage threshold for budget alerts
        'high_variance_threshold' => 20, // Percentage threshold for high-priority alerts
        'forecast_months' => 3, // Number of months to forecast
        'trend_analysis_months' => 12, // Number of months for trend analysis
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for dynamic pricing and affiliate calculations
    |
    */

    'pricing' => [
        'affiliate_discount_percentage' => 0.05, // 5% discount for affiliates
        'affiliate_commission_percentage' => 0.10, // 10% commission for affiliates
        'tier_offset' => 1, // Offset for tier calculations
    ],

    /*
    |--------------------------------------------------------------------------
    | Import/Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Excel import/export operations
    |
    */

    'import' => [
        'max_file_size' => 10240, // Maximum file size in KB (10MB)
        'allowed_extensions' => ['xlsx', 'xls'],
        'budget_template_start_row' => 4, // Row where data starts in budget template
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Common validation rules used across the application
    |
    */

    'validation' => [
        'year_range' => [
            'min' => 2020,
            'max' => 2100,
        ],
        'month_range' => [
            'min' => 1,
            'max' => 12,
        ],
    ],

];
