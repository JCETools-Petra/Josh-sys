<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('hotel_room_id')->nullable();
            $table->string('request_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable()->comment('For non-room specific issues (lobby, pool, etc)');
            $table->enum('category', ['plumbing', 'electrical', 'hvac', 'furniture', 'electronics', 'cleaning', 'painting', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'acknowledged', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('reported_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('reported_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('hotel_room_id')->references('id')->on('hotel_rooms')->onDelete('set null');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('property_id');
            $table->index('hotel_room_id');
            $table->index('status');
            $table->index('priority');
            $table->index('reported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
