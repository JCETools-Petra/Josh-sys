<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Riwayat Booking</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }} - Semua Riwayat Booking & Folio</p>
        </div>
        <a href="{{ route('frontoffice.index') }}"
            class="bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Front Office
        </a>
    </div>

    <!-- Success/Error Notifications -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 dark:border-green-400 rounded-lg shadow-lg">
        <div class="p-4">
            <div class="flex items-start">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-green-800 dark:text-green-200 mb-2">SUCCESS</h3>
                    <div class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 rounded-lg shadow-lg">
        <div class="p-4">
            <div class="flex items-start">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-800 dark:text-red-200 mb-2">ERROR</h3>
                    <div class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <form method="GET" action="{{ route('frontoffice.booking-history') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search by Guest -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cari Tamu
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama/Email/Telepon..."
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Room Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        No. Kamar
                    </label>
                    <input type="text" name="room" value="{{ request('room') }}"
                        placeholder="Cari no. kamar..."
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status
                    </label>
                    <select name="status"
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Check-in Dari
                    </label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Check-out Sampai
                    </label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('frontoffice.booking-history') }}"
                    class="bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white px-6 py-2 rounded-lg inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Menampilkan {{ $roomStays->count() }} dari {{ $roomStays->total() }} booking
    </div>

    <!-- Booking History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Konfirmasi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Tamu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Kamar
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Check-in
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Check-out
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Malam
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($roomStays as $stay)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $stay->confirmation_number }}
                            </div>
                            @if($stay->source)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($stay->source) }}
                                    @if($stay->ota_name)
                                        - {{ $stay->ota_name }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $stay->guest->full_name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $stay->guest->phone }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $stay->hotelRoom->room_number }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $stay->roomType->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $stay->check_in_date->format('d M Y') }}
                            </div>
                            @if($stay->actual_check_in)
                                <div class="text-xs text-green-600 dark:text-green-400">
                                    ✓ {{ $stay->actual_check_in->format('H:i') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $stay->check_out_date->format('d M Y') }}
                            </div>
                            @if($stay->actual_check_out)
                                <div class="text-xs text-green-600 dark:text-green-400">
                                    ✓ {{ $stay->actual_check_out->format('H:i') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $stay->nights }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusConfig = [
                                    'reserved' => ['text' => 'Reserved', 'class' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200'],
                                    'checked_in' => ['text' => 'Checked In', 'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200'],
                                    'checked_out' => ['text' => 'Checked Out', 'class' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'],
                                    'cancelled' => ['text' => 'Cancelled', 'class' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'],
                                    'no_show' => ['text' => 'No Show', 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'],
                                ];
                                $config = $statusConfig[$stay->status] ?? ['text' => $stay->status, 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'];
                            @endphp
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['class'] }}">
                                {{ $config['text'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Rp {{ number_format($stay->total_amount, 0, ',', '.') }}
                            </div>
                            @if($stay->payment_status)
                                @php
                                    $paymentConfig = [
                                        'paid' => ['text' => 'Lunas', 'class' => 'text-green-600 dark:text-green-400'],
                                        'partial' => ['text' => 'Sebagian', 'class' => 'text-yellow-600 dark:text-yellow-400'],
                                        'unpaid' => ['text' => 'Belum Bayar', 'class' => 'text-red-600 dark:text-red-400'],
                                    ];
                                    $payConfig = $paymentConfig[$stay->payment_status] ?? ['text' => $stay->payment_status, 'class' => 'text-gray-600 dark:text-gray-400'];
                                @endphp
                                <div class="text-xs {{ $payConfig['class'] }}">
                                    {{ $payConfig['text'] }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('frontoffice.folio', $stay->id) }}"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Lihat Folio
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Tidak Ada Data
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada riwayat booking yang ditemukan dengan filter yang dipilih.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $roomStays->links() }}
    </div>
</div>
</x-app-layout>
