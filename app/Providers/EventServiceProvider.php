<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// === AWAL PERBAIKAN ===
// Import Event dan Listener dari lokasi yang benar
use App\Events\OccupancyUpdated;
use App\Listeners\SendOccupancyUpdateNotification; // Untuk Email
use App\Listeners\SendOccupancyUpdateWhatsApp;    // Untuk WhatsApp
// === AKHIR PERBAIKAN ===

// Payment to Cash Drawer Integration
use App\Events\PaymentCreated;
use App\Listeners\SyncPaymentToCashDrawer;

// Front Office - Housekeeping Integration
use App\Events\RoomStatusChanged;
use App\Listeners\NotifyHousekeepingOfDirtyRoom;
use App\Listeners\NotifyFrontOfficeOfCleanRoom;
use App\Listeners\CreateHkTaskForDirtyRoom;

// Restaurant - Front Office Integration
use App\Events\FnbOrderCompleted;
use App\Listeners\AddChargeToRoomFolio;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Pastikan blok ini terlihat seperti ini
        OccupancyUpdated::class => [
            // SendOccupancyUpdateNotification::class, // Dinonaktifkan karena error SMTP
            SendOccupancyUpdateWhatsApp::class,
        ],

        // Payment to Cash Drawer Integration
        PaymentCreated::class => [
            SyncPaymentToCashDrawer::class,
        ],

        // Front Office - Housekeeping Integration
        RoomStatusChanged::class => [
            NotifyHousekeepingOfDirtyRoom::class,
            NotifyFrontOfficeOfCleanRoom::class,
            CreateHkTaskForDirtyRoom::class,
        ],

        // Restaurant - Front Office Integration
        FnbOrderCompleted::class => [
            AddChargeToRoomFolio::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}