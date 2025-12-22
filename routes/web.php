<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HotelRoomController as AdminHotelRoomController;
use App\Http\Controllers\Admin\IncomeController as AdminIncomeController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\MiceCategoryController;
use App\Http\Controllers\Admin\PricePackageController;
use App\Http\Controllers\Admin\PricingRuleController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\RevenueTargetController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Ecommerce\BarDisplayController;
use App\Http\Controllers\Ecommerce\DashboardController as EcommerceDashboardController;
use App\Http\Controllers\Ecommerce\ReservationController as EcommerceReservationController;
use App\Http\Controllers\Housekeeping\InventoryController;
use App\Http\Controllers\Inventory\DashboardController as InventoryDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyIncomeController;
use App\Http\Controllers\Sales\BookingController;
use App\Http\Controllers\Sales\CalendarController as SalesCalendarController;
use App\Http\Controllers\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Sales\DocumentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if (in_array($user->role, ['admin', 'owner', 'pengurus'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'pengguna_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'sales') {
            return redirect()->route('sales.dashboard');
        } elseif ($user->role === 'online_ecommerce') {
            return redirect()->route('ecommerce.dashboard');
        } elseif ($user->role === 'inventaris') {
            return redirect()->route('inventory.dashboard');
        } elseif ($user->role === 'hk') {
            return redirect()->route('housekeeping.inventory.index');
        }
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (in_array($user->role, ['admin', 'owner', 'pengurus'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'pengguna_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'online_ecommerce') {
            return redirect()->route('ecommerce.dashboard');
        } elseif ($user->role === 'sales') {
            return redirect()->route('sales.dashboard');
        } elseif ($user->role === 'inventaris') { // <-- INI BAGIAN YANG DIPERBAIKI
            return redirect()->route('inventory.dashboard'); // <-- INI BAGIAN YANG DIPERBAIKI
        } elseif ($user->role === 'hk') {
            return redirect()->route('housekeeping.inventory.index');
        }
        abort(403, 'Tidak ada dashboard yang sesuai untuk peran Anda.');
    })->name('dashboard');
});

// ============================================================================
// PMS (Property Management System) Routes
// ============================================================================

// Dashboard Analytics Route - Role: pengguna_properti, admin
Route::middleware(['auth', 'verified'])->get('/analytics', [\App\Http\Controllers\DashboardController::class, 'index'])->name('analytics');

// Front Office Routes - Role: pengguna_properti, admin
Route::middleware(['auth', 'verified'])->prefix('frontoffice')->name('frontoffice.')->group(function () {
    Route::get('/', [\App\Http\Controllers\FrontOfficeController::class, 'index'])->name('index');
    Route::get('/room-grid', [\App\Http\Controllers\FrontOfficeController::class, 'roomGrid'])->name('room-grid');

    // Reservation (from dashboard for future bookings)
    Route::post('/reservation', [\App\Http\Controllers\FrontOfficeController::class, 'createReservation'])->name('reservation');

    // Check-in / Check-out (Check-in from room grid)
    Route::post('/check-in', [\App\Http\Controllers\FrontOfficeController::class, 'checkIn'])->name('check-in');
    Route::get('/checkout/{roomStay}', [\App\Http\Controllers\FrontOfficeController::class, 'checkOut'])->name('checkout');
    Route::post('/checkout/{roomStay}/process', [\App\Http\Controllers\FrontOfficeController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/invoice/{roomStay}', [\App\Http\Controllers\FrontOfficeController::class, 'printInvoice'])->name('invoice');
    Route::get('/invoice/{roomStay}/pdf', [\App\Http\Controllers\FrontOfficeController::class, 'downloadInvoicePdf'])->name('invoice.pdf');
    Route::get('/mark-clean/{room}', [\App\Http\Controllers\FrontOfficeController::class, 'markRoomClean'])->name('mark-clean');

    // Room search
    Route::post('/search-rooms', [\App\Http\Controllers\FrontOfficeController::class, 'searchRooms'])->name('search-rooms');

    // Guest management
    Route::get('/search-guest', [\App\Http\Controllers\FrontOfficeController::class, 'searchGuest'])->name('search-guest');
    Route::get('/guest/{guest}', [\App\Http\Controllers\FrontOfficeController::class, 'showGuest'])->name('guest.show');
});

// Restaurant Routes - Role: pengguna_properti, admin
Route::middleware(['auth', 'verified'])->prefix('restaurant')->name('restaurant.')->group(function () {
    Route::get('/', [\App\Http\Controllers\RestaurantController::class, 'index'])->name('index');
    Route::get('/pos', [\App\Http\Controllers\RestaurantController::class, 'pos'])->name('pos');

    // Order management
    Route::post('/orders', [\App\Http\Controllers\RestaurantController::class, 'createOrder'])->name('orders.create');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\RestaurantController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/payment', [\App\Http\Controllers\RestaurantController::class, 'processPayment'])->name('orders.payment');

    // Menu management
    Route::get('/menu', [\App\Http\Controllers\RestaurantController::class, 'menuIndex'])->name('menu.index');
    Route::get('/menu/create', [\App\Http\Controllers\RestaurantController::class, 'menuCreate'])->name('menu.create');
    Route::post('/menu', [\App\Http\Controllers\RestaurantController::class, 'menuStore'])->name('menu.store');
    Route::get('/menu/{menuItem}/edit', [\App\Http\Controllers\RestaurantController::class, 'menuEdit'])->name('menu.edit');
    Route::put('/menu/{menuItem}', [\App\Http\Controllers\RestaurantController::class, 'menuUpdate'])->name('menu.update');
    Route::delete('/menu/{menuItem}', [\App\Http\Controllers\RestaurantController::class, 'menuDestroy'])->name('menu.destroy');
    Route::post('/menu/{menuItem}/toggle', [\App\Http\Controllers\RestaurantController::class, 'menuToggleAvailability'])->name('menu.toggle');
});

// Kitchen Display System Routes - Role: pengguna_properti, admin
Route::middleware(['auth', 'verified'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/display', [\App\Http\Controllers\KitchenDisplayController::class, 'index'])->name('display');
    Route::get('/orders', [\App\Http\Controllers\KitchenDisplayController::class, 'getOrders'])->name('orders');
});

// Reports Routes - Role: pengguna_properti, admin
Route::middleware(['auth', 'verified'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
    Route::get('/daily-sales', [\App\Http\Controllers\ReportController::class, 'dailySales'])->name('daily-sales');
    Route::get('/occupancy', [\App\Http\Controllers\ReportController::class, 'occupancy'])->name('occupancy');
    Route::get('/night-audit', [\App\Http\Controllers\ReportController::class, 'nightAudit'])->name('night-audit');
    Route::get('/fnb-sales', [\App\Http\Controllers\ReportController::class, 'fnbSales'])->name('fnb-sales');
});

// Housekeeping Routes - Role: hk, pengguna_properti, admin
Route::middleware(['auth', 'verified'])->prefix('housekeeping')->name('housekeeping.')->group(function () {
    // Note: housekeeping.inventory routes already exist, we add PMS routes here
    Route::get('/dashboard', [\App\Http\Controllers\HousekeepingController::class, 'index'])->name('dashboard');

    // Room status management
    Route::patch('/rooms/{room}/status', [\App\Http\Controllers\HousekeepingController::class, 'updateRoomStatus'])->name('rooms.update-status');
    Route::post('/rooms/{room}/mark-clean', [\App\Http\Controllers\HousekeepingController::class, 'markAsClean'])->name('rooms.mark-clean');
    Route::post('/rooms/bulk-mark-clean', [\App\Http\Controllers\HousekeepingController::class, 'bulkMarkAsClean'])->name('rooms.bulk-mark-clean');

    // Staff assignment
    Route::post('/rooms/{room}/assign', [\App\Http\Controllers\HousekeepingController::class, 'assignHousekeeper'])->name('rooms.assign');
});

require __DIR__ . '/auth.php';

// Grup Admin - Laporan
Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin,owner,pengurus'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-excel', [AdminDashboardController::class, 'exportExcel'])->name('dashboard.exportExcel');
    Route::get('/kpi-analysis', [AdminDashboardController::class, 'kpiAnalysis'])->name('kpi.analysis');
    Route::get('/kpi-analysis/export', [AdminDashboardController::class, 'exportKpiAnalysis'])->name('kpi.analysis.export');
    Route::get('/properties/compare', [AdminPropertyController::class, 'showComparisonForm'])->name('properties.compare_page');
    Route::get('/properties/compare/results', [AdminPropertyController::class, 'showComparisonResults'])->name('properties.compare.results');
    Route::get('properties/{property}', [AdminPropertyController::class, 'show'])->name('properties.show');
    Route::get('/inventories/export', [\App\Http\Controllers\Admin\InventoryController::class, 'exportExcel'])->name('inventories.export');
});

// Grup Admin - Manajemen
Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin,owner'])->name('admin.')->group(function () {
    Route::get('/sales-analytics', [AdminDashboardController::class, 'salesAnalytics'])->name('sales.analytics');
    Route::get('/calendar/unified', [AdminDashboardController::class, 'unifiedCalendar'])->name('calendar.unified');
    Route::get('/calendar/unified/events', [AdminDashboardController::class, 'getUnifiedCalendarEvents'])->name('calendar.unified.events');

    // Financial Budgeting & P&L Routes for Admin
    Route::get('/financial/select-property', [\App\Http\Controllers\Admin\FinancialController::class, 'selectProperty'])->name('financial.select-property');
    Route::get('/financial/{property}/dashboard', [\App\Http\Controllers\Admin\FinancialController::class, 'showDashboard'])->name('financial.dashboard');
    Route::get('/financial/{property}/input-actual', [\App\Http\Controllers\Admin\FinancialController::class, 'showInputActual'])->name('financial.input-actual');
    Route::post('/financial/{property}/input-actual', [\App\Http\Controllers\Admin\FinancialController::class, 'storeInputActual'])->name('financial.input-actual.store');
    Route::post('/financial/{property}/copy-previous-month', [\App\Http\Controllers\Admin\FinancialController::class, 'copyFromPreviousMonth'])->name('financial.copy-previous-month');
    Route::get('/financial/{property}/input-budget', [\App\Http\Controllers\Admin\FinancialController::class, 'showInputBudget'])->name('financial.input-budget');
    Route::post('/financial/{property}/input-budget', [\App\Http\Controllers\Admin\FinancialController::class, 'storeInputBudget'])->name('financial.input-budget.store');
    Route::get('/financial/{property}/budget-template/download', [\App\Http\Controllers\Admin\FinancialController::class, 'downloadBudgetTemplate'])->name('financial.budget-template.download');
    Route::post('/financial/{property}/budget-template/import', [\App\Http\Controllers\Admin\FinancialController::class, 'importBudgetTemplate'])->name('financial.budget-template.import');
    Route::get('/financial/{property}/report', [\App\Http\Controllers\Admin\FinancialController::class, 'showReport'])->name('financial.report');
    Route::get('/financial/{property}/export-excel', [\App\Http\Controllers\Admin\FinancialController::class, 'exportExcel'])->name('financial.export-excel');
    Route::get('/financial/{property}/export-pdf', [\App\Http\Controllers\Admin\FinancialController::class, 'exportPdf'])->name('financial.export-pdf');

    // Budget Debugging Routes for Admin
    Route::get('/financial/{property}/debug/verify', [\App\Http\Controllers\Admin\BudgetDebugController::class, 'verify'])->name('financial.debug.verify');
    Route::get('/financial/{property}/debug/show', [\App\Http\Controllers\Admin\BudgetDebugController::class, 'show'])->name('financial.debug.show');
    Route::get('/financial/{property}/debug/api', [\App\Http\Controllers\Admin\BudgetDebugController::class, 'api'])->name('financial.debug.api');

    // Financial Category Management for Admin
    Route::resource('financial-categories', \App\Http\Controllers\Admin\FinancialCategoryController::class);

    Route::resource('users', AdminUserController::class);
    Route::get('/users-trashed', [AdminUserController::class, 'trashed'])->name('users.trashed');
    Route::post('/users/{user}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{user}/force-delete', [AdminUserController::class, 'forceDelete'])->name('users.force-delete');
    Route::resource('properties', AdminPropertyController::class)->except(['show']);
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::get('inventories/select', [AdminInventoryController::class, 'select'])->name('inventories.select');
    Route::resource('inventories', AdminInventoryController::class);
    Route::resource('revenue-targets', RevenueTargetController::class);
    Route::resource('targets', TargetController::class);
    Route::resource('mice-categories', MiceCategoryController::class);
    Route::resource('price-packages', PricePackageController::class);
    Route::get('/reports/amenities', [AdminInventoryController::class, 'report'])->name('reports.amenities');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity_log.index');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    Route::resource('properties.rooms', AdminRoomController::class)->shallow();
    Route::resource('properties.hotel_rooms', AdminHotelRoomController::class)->shallow()->names('properties.hotel-rooms');

    // Hotel Rooms Management Routes (without property binding)
    Route::get('/hotel-rooms', [AdminHotelRoomController::class, 'index'])->name('hotel-rooms.index');
    Route::get('/hotel-rooms/create', [AdminHotelRoomController::class, 'create'])->name('hotel-rooms.create');
    Route::post('/hotel-rooms', [AdminHotelRoomController::class, 'store'])->name('hotel-rooms.store');
    Route::get('/hotel-rooms/{hotelRoom}', [AdminHotelRoomController::class, 'show'])->name('hotel-rooms.show');
    Route::get('/hotel-rooms/{hotelRoom}/edit', [AdminHotelRoomController::class, 'edit'])->name('hotel-rooms.edit');
    Route::put('/hotel-rooms/{hotelRoom}', [AdminHotelRoomController::class, 'update'])->name('hotel-rooms.update');
    Route::delete('/hotel-rooms/{hotelRoom}', [AdminHotelRoomController::class, 'destroy'])->name('hotel-rooms.destroy');
    Route::post('/hotel-rooms/bulk-status', [AdminHotelRoomController::class, 'bulkUpdateStatus'])->name('hotel-rooms.bulk-status');
    Route::get('/hotel-rooms/room-types/{property}', [AdminHotelRoomController::class, 'getRoomTypes'])->name('hotel-rooms.room-types');

    Route::resource('properties.incomes', AdminIncomeController::class)->shallow();
    Route::post('properties/{property}/occupancy', [AdminPropertyController::class, 'updateOccupancy'])->name('properties.occupancy.update');
    Route::prefix('properties/{property}/pricing-rule')->name('pricing-rules.')->group(function () {
        Route::get('/', [PricingRuleController::class, 'index'])->name('index');
        Route::post('/store-room-type', [PricingRuleController::class, 'storeRoomType'])->name('room-type.store');
        Route::put('/update-pricing-rule/{roomType}', [PricingRuleController::class, 'updatePricingRule'])->name('rule.update');
        Route::delete('/destroy-room-type/{roomType}', [PricingRuleController::class, 'destroyRoomType'])->name('room-type.destroy');
        Route::put('/update-property-bars', [PricingRuleController::class, 'updatePropertyBars'])->name('property-bars.update');
    });
    Route::get('/inventories/export', [\App\Http\Controllers\Admin\InventoryController::class, 'exportExcel'])->name('inventories.export');
    Route::post('/settings/test-msq-email', [\App\Http\Controllers\Admin\SettingController::class, 'sendTestMsqEmail'])->name('settings.testMsqEmail');
});

// Grup Sales
Route::prefix('sales')->middleware(['auth', 'verified', 'role:admin,sales,owner'])->name('sales.')->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class, 'index'])->name('dashboard');
    Route::resource('bookings', BookingController::class);
    Route::get('/bookings/{booking}/download-beo', [BookingController::class, 'downloadBeo'])->name('bookings.download_beo');
    Route::get('/bookings/{booking}/show-beo', [BookingController::class, 'showBeo'])->name('bookings.show_beo');
    Route::get('/bookings/{booking}/beo', [BookingController::class, 'beo'])->name('bookings.beo');
    Route::post('/bookings/{booking}/beo', [BookingController::class, 'storeBeo'])->name('bookings.storeBeo');
    Route::get('/bookings/{booking}/beo/show', [BookingController::class, 'showBeo'])->name('bookings.showBeo');
    Route::get('/bookings/{booking}/beo/print', [BookingController::class, 'printBeo'])->name('bookings.printBeo');
    Route::get('/bookings/{booking}/quotation', [DocumentController::class, 'generateQuotation'])->name('documents.quotation');
    Route::get('/bookings/{booking}/invoice', [DocumentController::class, 'generateInvoice'])->name('documents.invoice');
    Route::get('/bookings/{booking}/beo/pdf', [DocumentController::class, 'generateBeo'])->name('documents.beo');
    Route::get('/calendar', [SalesCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [SalesCalendarController::class, 'events'])->name('calendar.events');
});

// Grup Housekeeping
Route::prefix('housekeeping')->middleware(['auth', 'verified', 'role:hk,owner'])->name('housekeeping.')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/select-room', [InventoryController::class, 'selectRoom'])->name('inventory.select-room');
    Route::get('/inventory/assign/{room}', [InventoryController::class, 'assign'])->name('inventory.assign');
    Route::post('/inventory/update/{room}', [InventoryController::class, 'updateInventory'])->name('inventory.update');
    Route::get('/history', [InventoryController::class, 'history'])->name('inventory.history');
});

// Grup Pengguna Properti
Route::prefix('property')->middleware(['auth', 'verified', 'role:pengguna_properti,owner'])->name('property.')->group(function () {
    Route::get('/dashboard', [PropertyIncomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/calendar', [PropertyIncomeController::class, 'calendar'])->name('calendar.index');
    Route::get('/calendar-data', [PropertyIncomeController::class, 'getCalendarData'])->name('calendar.data');
    Route::get('properties/{property}/room-types', [EcommerceReservationController::class, 'getRoomTypesByProperty'])->name('properties.room-types');
    Route::get('room-types/{roomType}/active-price', [EcommerceReservationController::class, 'getActiveBarPrice'])->name('room-types.active-price');
    Route::resource('reservations', EcommerceReservationController::class);
    Route::get('/income', [PropertyIncomeController::class, 'index'])->name('income.index');
    Route::get('/income/create', [PropertyIncomeController::class, 'create'])->name('income.create');
    Route::post('/income', [PropertyIncomeController::class, 'store'])->name('income.store');
    Route::get('/income/{income}/edit', [PropertyIncomeController::class, 'edit'])->name('income.edit');
    Route::put('/income/{income}', [PropertyIncomeController::class, 'update'])->name('income.update');
    Route::delete('/income/{income}', [PropertyIncomeController::class, 'destroy'])->name('income.destroy');
    Route::post('/occupancy/update', [PropertyIncomeController::class, 'updateOccupancy'])->name('occupancy.update');

    // Financial P&L Routes (Property users can only input actual data, NOT budget)
    Route::get('/financial/dashboard', [\App\Http\Controllers\FinancialController::class, 'showDashboard'])->name('financial.dashboard');
    Route::get('/financial/input-actual', [\App\Http\Controllers\FinancialController::class, 'showInputActual'])->name('financial.input-actual');
    Route::post('/financial/input-actual', [\App\Http\Controllers\FinancialController::class, 'storeInputActual'])->name('financial.input-actual.store');
    Route::post('/financial/copy-previous-month', [\App\Http\Controllers\FinancialController::class, 'copyFromPreviousMonth'])->name('financial.copy-previous-month');
    Route::get('/financial/report', [\App\Http\Controllers\FinancialController::class, 'showReport'])->name('financial.report');
    Route::get('/financial/export-excel', [\App\Http\Controllers\FinancialController::class, 'exportExcel'])->name('financial.export-excel');
    Route::get('/financial/export-pdf', [\App\Http\Controllers\FinancialController::class, 'exportPdf'])->name('financial.export-pdf');
});

// Grup E-commerce
Route::prefix('ecommerce')->middleware(['auth', 'verified', 'role:online_ecommerce'])->name('ecommerce.')->group(function () {
    Route::get('/dashboard', [EcommerceDashboardController::class, 'index'])->name('dashboard');
    Route::resource('reservations', EcommerceReservationController::class);
    Route::get('/bar-prices', [BarDisplayController::class, 'index'])->name('bar-prices.index');
});

// Grup Inventaris
Route::prefix('inventory')->middleware(['auth', 'verified', 'role:inventaris,owner,admin'])->name('inventory.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('inventory.dashboard');
    });
    
    // Rute utama (dashboard) sekarang mengarah ke ItemController
    Route::get('/dashboard', [\App\Http\Controllers\Inventory\ItemController::class, 'index'])->name('dashboard');

    // Resource routes untuk Item dan Kategori
    Route::resource('items', \App\Http\Controllers\Inventory\ItemController::class)->except(['show', 'index']);
    Route::resource('categories', \App\Http\Controllers\Inventory\CategoryController::class)->except(['show']);
});