<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header with Search -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Front Office - {{ $property->name }}</h1>
            <p class="text-gray-600 dark:text-gray-400">Property Management System</p>
        </div>
        <div class="flex space-x-3">
            <!-- Guest Search -->
            <div class="relative">
                <input type="text" id="guest_search" placeholder="Cari tamu (Nama/Phone/Email)..."
                    class="w-80 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 rounded-lg shadow-sm pl-10 pr-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <!-- Search Results Dropdown -->
                <div id="search_results" class="hidden absolute top-full mt-2 w-full bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Notifications -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 dark:border-green-400 rounded-lg shadow-lg animate-pulse">
        <div class="p-4">
            <div class="flex items-start">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-green-800 dark:text-green-200 mb-2">SUCCESS</h3>
                    <div class="text-sm text-green-700 dark:text-green-300 whitespace-pre-line">{{ session('success') }}</div>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 ml-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 rounded-lg shadow-lg animate-pulse">
        <div class="p-4">
            <div class="flex items-start">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-800 dark:text-red-200 mb-2">ERROR</h3>
                    <div class="text-sm text-red-700 dark:text-red-300 whitespace-pre-line">{{ session('error') }}</div>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 ml-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <!-- Total Rooms -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Kamar</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $totalRooms }}</div>
        </div>

        <!-- Occupied -->
        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg shadow border-l-4 border-blue-500 dark:border-blue-400 p-4">
            <div class="text-sm text-blue-600 dark:text-blue-300">Terisi</div>
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-200">{{ $occupiedRooms }}</div>
        </div>

        <!-- Available -->
        <div class="bg-green-50 dark:bg-green-900/30 rounded-lg shadow border-l-4 border-green-500 dark:border-green-400 p-4">
            <div class="text-sm text-green-600 dark:text-green-300">Siap</div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-200">{{ $availableRoomsCount }}</div>
        </div>

        <!-- Dirty -->
        <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg shadow border-l-4 border-yellow-500 dark:border-yellow-400 p-4">
            <div class="text-sm text-yellow-600 dark:text-yellow-300">Kotor</div>
            <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-200">{{ $dirtyRooms }}</div>
        </div>

        <!-- Maintenance -->
        <div class="bg-red-50 dark:bg-red-900/30 rounded-lg shadow border-l-4 border-red-500 dark:border-red-400 p-4">
            <div class="text-sm text-red-600 dark:text-red-300">Perbaikan</div>
            <div class="text-2xl font-bold text-red-700 dark:text-red-200">{{ $maintenanceRooms }}</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <button onclick="showReservationModal()"
           class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow p-6 text-center transition">
            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <div class="text-lg font-bold">Buat Reservasi</div>
        </button>

        <a href="{{ route('frontoffice.room-grid') }}"
           class="bg-green-600 hover:bg-green-700 text-white rounded-lg shadow p-6 text-center transition">
            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"></path>
            </svg>
            <div class="text-lg font-bold">Room Grid</div>
        </a>

        <a href="{{ route('restaurant.index') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white rounded-lg shadow p-6 text-center transition">
            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <div class="text-lg font-bold">Restaurant</div>
        </a>
    </div>

    <!-- ALERTS: Overdue & Pending Checkout -->
    @if($overdueCheckout->count() > 0 || $pendingCheckoutToday->count() > 0)
    <div class="mb-6 space-y-4">
        <!-- OVERDUE CHECKOUT ALERT (MERAH - URGENT!) -->
        @if($overdueCheckout->count() > 0)
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 rounded-lg shadow-lg">
            <div class="p-4">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-red-800 dark:text-red-200 mb-2">⚠️ OVERDUE CHECKOUT - {{ $overdueCheckout->count() }} Tamu</h3>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-3">Tamu berikut SUDAH LEWAT waktu checkout dan harus SEGERA diproses!</p>

                        <div class="space-y-2">
                            @foreach($overdueCheckout as $stay)
                            <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-700 rounded-lg p-3 flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-gray-100">{{ $stay->guest->full_name }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Kamar {{ $stay->hotelRoom->room_number }} •
                                        <span class="text-red-600 font-semibold">
                                            Checkout seharusnya: {{ $stay->check_out_date->format('d M Y H:i') }}
                                            ({{ $stay->check_out_date->diffForHumans() }})
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('frontoffice.checkout', $stay) }}"
                                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                                    Checkout Sekarang
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- PENDING CHECKOUT TODAY (KUNING - WARNING) -->
        @if($pendingCheckoutToday->count() > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg shadow">
            <div class="p-4">
                <div class="flex items-start">
                    <svg class="w-7 h-7 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-yellow-800 mb-2">📋 Checkout Hari Ini - {{ $pendingCheckoutToday->count() }} Tamu</h3>
                        <p class="text-sm text-yellow-700 mb-3">Tamu yang dijadwalkan checkout hari ini (belum diproses):</p>

                        <div class="space-y-2">
                            @foreach($pendingCheckoutToday as $stay)
                            <div class="bg-white border border-yellow-200 rounded-lg p-3 flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-gray-800">{{ $stay->guest->full_name }}</div>
                                    <div class="text-sm text-gray-600">
                                        Kamar {{ $stay->hotelRoom->room_number }} •
                                        Checkout: {{ $stay->check_out_date->format('H:i') }}
                                    </div>
                                </div>
                                <a href="{{ route('frontoffice.checkout', $stay) }}"
                                   class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                                    Proses Checkout
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- 🔧 BUG FIX: Add Pending Check-In section for reservations that need to be checked in --}}
    @if($pendingCheckInToday->count() > 0)
    <div class="mb-6">
        <div class="bg-indigo-50 border-l-4 border-indigo-500 rounded-lg shadow-lg">
            <div class="p-4">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-indigo-900">Reservasi Perlu Check-In ({{ $pendingCheckInToday->count() }})</h3>
                        <p class="text-sm text-indigo-700 mb-3">Reservasi yang dijadwalkan check-in hari ini dan perlu diproses</p>
                        <div class="space-y-2">
                            @foreach($pendingCheckInToday as $reservation)
                            <div class="bg-white rounded-lg p-3 border border-indigo-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900">{{ $reservation->guest->full_name }}</div>
                                        <div class="text-sm text-gray-600">
                                            Kamar {{ $reservation->hotelRoom->room_number }} •
                                            Check-in: {{ $reservation->check_in_date->format('d M Y H:i') }} •
                                            {{ $reservation->nights }} malam
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Konfirmasi: {{ $reservation->confirmation_number }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <a href="{{ route('frontoffice.folio', $reservation) }}"
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Lihat Folio
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Today's Activities -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Check-in Today -->
        <div class="bg-white rounded-lg shadow">
            <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg">
                <h2 class="text-lg font-bold">Check-in Hari Ini ({{ $checkingInToday->count() }})</h2>
                <p class="text-xs text-blue-100 mt-1">Tamu yang melakukan check-in hari ini ({{ now()->format('d M Y') }})</p>
            </div>
            <div class="p-4">
                @forelse($checkingInToday as $stay)
                <div class="border-b py-3 last:border-0">
                    <div class="font-semibold">{{ $stay->guest->full_name }}</div>
                    <div class="text-sm text-gray-600">
                        Kamar {{ $stay->hotelRoom->room_number }} •
                        {{ $stay->check_in_date->format('H:i') }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Tidak ada check-in hari ini</p>
                @endforelse
            </div>
        </div>

        <!-- Check-out Today -->
        <div class="bg-white rounded-lg shadow">
            <div class="bg-green-600 text-white px-6 py-3 rounded-t-lg">
                <h2 class="text-lg font-bold">Check-out Hari Ini ({{ $checkingOutToday->count() }})</h2>
                <p class="text-xs text-green-100 mt-1">Tamu yang sudah check-out hari ini ({{ now()->format('d M Y') }})</p>
            </div>
            <div class="p-4">
                @forelse($checkingOutToday as $stay)
                <div class="border-b py-3 last:border-0">
                    <div class="font-semibold">{{ $stay->guest->full_name }}</div>
                    <div class="text-sm text-gray-600">
                        Kamar {{ $stay->hotelRoom->room_number }} •
                        {{ $stay->actual_check_out ? \Carbon\Carbon::parse($stay->actual_check_out)->format('H:i') : $stay->check_out_date->format('H:i') }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Tidak ada check-out hari ini</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Current Guests -->
    <div class="bg-white rounded-lg shadow">
        <div class="bg-gray-800 text-white px-6 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Tamu Saat Ini ({{ $currentGuests->count() }})</h2>
            <p class="text-xs text-gray-300 mt-1">Semua tamu yang sedang menginap (termasuk yang check-in kemarin/sebelumnya)</p>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Kamar</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Tamu</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Tipe Kamar</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Check-in</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Check-out</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Malam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($currentGuests as $stay)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">{{ $stay->hotelRoom->room_number }}</td>
                            <td class="px-4 py-3">{{ $stay->guest->full_name }}</td>
                            <td class="px-4 py-3">{{ $stay->roomType->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $stay->check_in_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm">{{ $stay->check_out_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm">{{ $stay->nights }} malam</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada tamu saat ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reservation Modal -->
<div id="reservationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <form action="{{ route('frontoffice.reservation') }}" method="POST">
            @csrf

            <!-- Modal Header -->
            <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center sticky top-0">
                <h2 class="text-xl font-bold">Buat Reservasi Baru</h2>
                <button type="button" onclick="closeReservationModal()" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <!-- Room Selection -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">1. Pilih Kamar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kamar Tersedia *</label>
                            <select name="hotel_room_id" id="modal_hotel_room_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Pilih Kamar --</option>
                                @foreach($availableRooms as $room)
                                <option value="{{ $room->id }}" data-type="{{ $room->roomType->name ?? 'Standard' }}" data-price="{{ $room->roomType->price ?? 0 }}">
                                    Room {{ $room->room_number }}
                                    @if($room->floor) - Lantai {{ $room->floor }} @endif
                                    ({{ $room->roomType->name ?? 'Standard' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Malam (Rp) *</label>
                            <input type="number" name="room_rate_per_night" id="modal_room_rate_per_night" required min="0" step="1000"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Guest Information -->
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <h3 class="text-lg font-semibold text-green-900 mb-4">2. Data Tamu</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Depan *</label>
                            <input type="text" name="guest[first_name]" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Belakang</label>
                            <input type="text" name="guest[last_name]"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="guest[email]"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon *</label>
                            <input type="text" name="guest[phone]" required placeholder="08123456789"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Identitas *</label>
                            <select name="guest[id_type]" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                                <option value="ktp">KTP</option>
                                <option value="passport">Passport</option>
                                <option value="sim">SIM</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Identitas *</label>
                            <input type="text" name="guest[id_number]" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea name="guest[address]" rows="2"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                            <input type="text" name="guest[city]"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <!-- Stay Information -->
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">3. Detail Reservasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Check-In *</label>
                            <input type="date" name="check_in_date" id="modal_check_in_date" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                value="{{ now()->addDays(1)->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Check-Out *</label>
                            <input type="date" name="check_out_date" id="modal_check_out_date" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                value="{{ now()->addDays(2)->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Malam</label>
                            <input type="text" id="modal_nights_display" readonly
                                class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-100" value="1 malam">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dewasa *</label>
                            <input type="number" name="adults" required min="1" value="1"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Anak-anak</label>
                            <input type="number" name="children" min="0" value="0"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                </div>

                <!-- Booking Source -->
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <h3 class="text-lg font-semibold text-yellow-900 mb-4">4. Sumber Booking</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sumber *</label>
                            <select name="source" id="modal_source" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="walk_in">Walk-In</option>
                                <option value="ota">OTA (Online Travel Agent)</option>
                                <option value="ta">Travel Agent</option>
                                <option value="corporate">Corporate</option>
                                <option value="government">Government</option>
                                <option value="compliment">Compliment</option>
                                <option value="house_use">House Use</option>
                                <option value="affiliate">Affiliate</option>
                                <option value="online">Online Direct</option>
                            </select>
                        </div>
                        <div id="modal_ota_fields" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama OTA</label>
                            <input type="text" name="ota_name"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Contoh: Traveloka, Booking.com">
                        </div>
                        <div id="modal_booking_id_field" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Booking ID / Kode</label>
                            <input type="text" name="ota_booking_id"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permintaan Khusus</label>
                            <textarea name="special_requests" rows="2"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Extra bed, high floor, allergies, dll"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Deposit -->
                <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
                    <h3 class="text-lg font-semibold text-indigo-900 mb-4">5. Deposit (Opsional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Deposit Diminta (Rp)</label>
                            <input type="number" name="deposit_amount" id="modal_deposit_amount" min="0" step="10000"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="0" onchange="calculateModalDepositBalance()">
                            <p class="mt-1 text-xs text-gray-500">Deposit yang diminta dari tamu (biasanya 30-50% dari total)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deposit Sudah Dibayar (Rp)</label>
                            <input type="number" name="deposit_paid" id="modal_deposit_paid" min="0" step="10000"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="0" onchange="calculateModalDepositBalance()">
                            <p class="mt-1 text-xs text-gray-500">Jumlah deposit yang sudah diterima saat ini</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                            <select name="payment_method" id="modal_payment_method" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div id="modal_deposit_balance_info" class="md:col-span-3 hidden">
                            <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Sisa Deposit yang Belum Dibayar:</span>
                                    <span class="text-lg font-bold" id="modal_deposit_balance_display">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-gray-50 border border-gray-300 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Biaya</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga per malam:</span>
                            <span class="font-semibold" id="modal_rate_display">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jumlah malam:</span>
                            <span class="font-semibold" id="modal_nights_summary">1</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold" id="modal_subtotal_display">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Pajak (10%):</span>
                            <span id="modal_tax_display">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Service Charge (5%):</span>
                            <span id="modal_service_display">Rp 0</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between text-lg font-bold text-blue-600">
                            <span>Total:</span>
                            <span id="modal_total_display">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end space-x-3 sticky bottom-0">
                <button type="button" onclick="closeReservationModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Reservasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Functions
function showReservationModal() {
    document.getElementById('reservationModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReservationModal() {
    document.getElementById('reservationModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('reservationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReservationModal();
    }
});

// Check-In Form Logic
document.addEventListener('DOMContentLoaded', function() {
    const roomSelect = document.getElementById('modal_hotel_room_id');
    const rateInput = document.getElementById('modal_room_rate_per_night');
    const checkInInput = document.getElementById('modal_check_in_date');
    const checkOutInput = document.getElementById('modal_check_out_date');
    const sourceSelect = document.getElementById('modal_source');
    const otaFields = document.getElementById('modal_ota_fields');
    const bookingIdField = document.getElementById('modal_booking_id_field');

    // Auto-fill rate when room is selected
    roomSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        if (price) {
            rateInput.value = price;
            updateModalSummary();
        }
    });

    // Show/hide OTA fields
    sourceSelect.addEventListener('change', function() {
        if (this.value === 'ota') {
            otaFields.style.display = 'block';
            bookingIdField.style.display = 'block';
        } else {
            otaFields.style.display = 'none';
            bookingIdField.style.display = 'none';
        }
    });

    // Calculate nights and update summary
    function calculateModalNights() {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        if (checkIn && checkOut && checkOut > checkIn) {
            const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            document.getElementById('modal_nights_display').value = nights + ' malam';
            document.getElementById('modal_nights_summary').textContent = nights;
            return nights;
        }
        return 1;
    }

    function formatRupiah(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateModalSummary() {
        const rate = parseFloat(rateInput.value) || 0;
        const nights = calculateModalNights();
        const subtotal = rate * nights;
        const tax = subtotal * 0.10;
        const service = subtotal * 0.05;
        const total = subtotal + tax + service;

        document.getElementById('modal_rate_display').textContent = formatRupiah(rate);
        document.getElementById('modal_subtotal_display').textContent = formatRupiah(subtotal);
        document.getElementById('modal_tax_display').textContent = formatRupiah(tax);
        document.getElementById('modal_service_display').textContent = formatRupiah(service);
        document.getElementById('modal_total_display').textContent = formatRupiah(total);
    }

    function calculateModalDepositBalance() {
        const depositAmount = parseFloat(document.getElementById('modal_deposit_amount').value) || 0;
        const depositPaid = parseFloat(document.getElementById('modal_deposit_paid').value) || 0;
        const balance = Math.max(0, depositAmount - depositPaid);

        const balanceInfo = document.getElementById('modal_deposit_balance_info');
        const balanceDisplay = document.getElementById('modal_deposit_balance_display');

        if (depositAmount > 0) {
            balanceInfo.classList.remove('hidden');
            balanceDisplay.textContent = formatRupiah(balance);

            // Change color based on status
            const balanceDiv = balanceInfo.querySelector('div');
            if (balance === 0 && depositPaid > 0) {
                // Fully paid
                balanceDiv.className = 'bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded';
            } else if (depositPaid > 0 && balance > 0) {
                // Partially paid
                balanceDiv.className = 'bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded';
            } else {
                // Not paid yet
                balanceDiv.className = 'bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded';
            }
        } else {
            balanceInfo.classList.add('hidden');
        }
    }

    rateInput.addEventListener('input', updateModalSummary);
    checkInInput.addEventListener('change', updateModalSummary);
    checkOutInput.addEventListener('change', updateModalSummary);

    // Initial calculation
    updateModalSummary();

    // Guest Search Functionality
    const searchInput = document.getElementById('guest_search');
    const searchResults = document.getElementById('search_results');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/frontoffice/search-guest?q=${encodeURIComponent(query)}`);
                const results = await response.json();

                if (results.length === 0) {
                    searchResults.innerHTML = '<div class="p-4 text-gray-500 text-center">Tidak ada hasil ditemukan</div>';
                } else {
                    searchResults.innerHTML = results.map(guest => `
                        <div class="p-4 hover:bg-gray-50 cursor-pointer border-b last:border-0" onclick="viewGuestDetail(${guest.id})">
                            <div class="font-semibold text-gray-800">${guest.full_name}</div>
                            <div class="text-sm text-gray-600">${guest.phone} • ${guest.email || '-'}</div>
                            ${guest.current_stay ? `
                                <div class="text-xs text-blue-600 mt-1">
                                    🏨 Currently staying in Room ${guest.current_stay.room_number}
                                </div>
                            ` : ''}
                        </div>
                    `).join('');
                }

                searchResults.classList.remove('hidden');
            } catch (error) {
                console.error('Search error:', error);
            }
        }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
});

function viewGuestDetail(guestId) {
    // For now, just show alert. Later can open modal with full guest history
    window.location.href = `/frontoffice/guest/${guestId}`;
}
</script>
</x-app-layout>
