<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Performance indexes to optimize frequently used queries.
     */
    public function up(): void
    {
        // Daily Incomes - frequently queried by property_id and date
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->index('date', 'idx_daily_incomes_date');
            $table->index(['property_id', 'date'], 'idx_daily_incomes_property_date');
        });

        // Daily Occupancies - queried by property_id and date
        Schema::table('daily_occupancies', function (Blueprint $table) {
            $table->index('date', 'idx_daily_occupancies_date');
            $table->index(['property_id', 'date'], 'idx_daily_occupancies_property_date');
        });

        // Bookings - frequently queried by event_date and status
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('event_date', 'idx_bookings_event_date');
            $table->index(['property_id', 'event_date'], 'idx_bookings_property_event_date');
            $table->index(['status', 'event_date'], 'idx_bookings_status_event_date');
        });

        // Financial Entries - queried by property, category, year, month
        Schema::table('financial_entries', function (Blueprint $table) {
            $table->index(['property_id', 'year', 'month'], 'idx_financial_entries_property_year_month');
            $table->index(['financial_category_id', 'year', 'month'], 'idx_financial_entries_category_year_month');
        });

        // Financial Categories - hierarchical queries
        Schema::table('financial_categories', function (Blueprint $table) {
            $table->index(['property_id', 'parent_id'], 'idx_financial_categories_property_parent');
            $table->index(['property_id', 'type'], 'idx_financial_categories_property_type');
        });

        // Reservations - queried by checkin/checkout dates
        Schema::table('reservations', function (Blueprint $table) {
            $table->index('checkin_date', 'idx_reservations_checkin_date');
            $table->index(['property_id', 'checkin_date'], 'idx_reservations_property_checkin');
        });

        // Users - queried by role and property
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
            $table->index(['property_id', 'role'], 'idx_users_property_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->dropIndex('idx_daily_incomes_date');
            $table->dropIndex('idx_daily_incomes_property_date');
        });

        Schema::table('daily_occupancies', function (Blueprint $table) {
            $table->dropIndex('idx_daily_occupancies_date');
            $table->dropIndex('idx_daily_occupancies_property_date');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_event_date');
            $table->dropIndex('idx_bookings_property_event_date');
            $table->dropIndex('idx_bookings_status_event_date');
        });

        Schema::table('financial_entries', function (Blueprint $table) {
            $table->dropIndex('idx_financial_entries_property_year_month');
            $table->dropIndex('idx_financial_entries_category_year_month');
        });

        Schema::table('financial_categories', function (Blueprint $table) {
            $table->dropIndex('idx_financial_categories_property_parent');
            $table->dropIndex('idx_financial_categories_property_type');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('idx_reservations_checkin_date');
            $table->dropIndex('idx_reservations_property_checkin');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_property_role');
        });
    }
};
