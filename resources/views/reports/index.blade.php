<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
        <p class="text-gray-600">{{ $property->name }}</p>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Daily Sales Report -->
        <a href="{{ route('reports.daily-sales') }}" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-xl font-bold mb-2">Daily Sales Report</h3>
            <p class="text-blue-100 text-sm">Laporan penjualan harian room & F&B</p>
        </a>

        <!-- Occupancy Report -->
        <a href="{{ route('reports.occupancy') }}" class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <h3 class="text-xl font-bold mb-2">Occupancy Report</h3>
            <p class="text-green-100 text-sm">Tingkat hunian kamar per periode</p>
        </a>

        <!-- Night Audit -->
        <a href="{{ route('reports.night-audit') }}" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
            <h3 class="text-xl font-bold mb-2">Night Audit</h3>
            <p class="text-purple-100 text-sm">Laporan penutupan hari (End of Day)</p>
        </a>

        <!-- F&B Sales Report -->
        <a href="{{ route('reports.fnb-sales') }}" class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-xl font-bold mb-2">F&B Sales Report</h3>
            <p class="text-orange-100 text-sm">Laporan penjualan restaurant & best sellers</p>
        </a>

        <!-- Revenue Report -->
        <a href="#" onclick="alert('Coming soon!')" class="bg-gradient-to-br from-teal-500 to-teal-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-xl font-bold mb-2">Revenue Report</h3>
            <p class="text-teal-100 text-sm">Analisa pendapatan per periode</p>
        </a>

        <!-- Guest Ledger -->
        <a href="#" onclick="alert('Coming soon!')" class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition transform hover:scale-105">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-xl font-bold mb-2">Guest Ledger</h3>
            <p class="text-pink-100 text-sm">Outstanding payments & billing</p>
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Today's Revenue</div>
            <div class="text-2xl font-bold text-gray-800">Rp {{ number_format(\App\Models\RoomStay::where('property_id', $property->id)->whereDate('check_in_date', '<=', today())->whereDate('check_out_date', '>=', today())->sum('total_room_charge'), 0, ',', '.') }}</div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Occupancy Today</div>
            @php
                $totalRooms = \App\Models\HotelRoom::where('property_id', $property->id)->count();
                $occupied = \App\Models\RoomStay::where('property_id', $property->id)->whereDate('check_in_date', '<=', today())->whereDate('check_out_date', '>', today())->where('status', 'checked_in')->count();
                $occRate = $totalRooms > 0 ? ($occupied / $totalRooms) * 100 : 0;
            @endphp
            <div class="text-2xl font-bold text-gray-800">{{ number_format($occRate, 1) }}%</div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Check-ins Today</div>
            <div class="text-2xl font-bold text-gray-800">{{ \App\Models\RoomStay::where('property_id', $property->id)->whereDate('actual_check_in', today())->count() }}</div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Check-outs Today</div>
            <div class="text-2xl font-bold text-gray-800">{{ \App\Models\RoomStay::where('property_id', $property->id)->whereDate('actual_check_out', today())->count() }}</div>
        </div>
    </div>
</div>
</x-app-layout>
