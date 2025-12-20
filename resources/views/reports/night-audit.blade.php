<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Night Audit Report</h1>
            <p class="text-gray-600">{{ $property->name }} - {{ $targetDate->format('d M Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <form method="GET" class="flex space-x-2">
                <input type="date" name="date" value="{{ $targetDate->toDateString() }}" class="border-gray-300 rounded-lg shadow-sm">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Filter
                </button>
            </form>
            <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                Print
            </button>
            <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Kembali
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-blue-600">Occupancy Rate</div>
            <div class="text-2xl font-bold text-blue-700">{{ number_format($occupancyRate, 1) }}%</div>
            <div class="text-xs text-gray-600">{{ $occupiedRooms->count() }}/{{ $totalRooms }} rooms</div>
        </div>

        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600">ADR (Avg Daily Rate)</div>
            <div class="text-2xl font-bold text-green-700">Rp {{ number_format($adr, 0, ',', '.') }}</div>
        </div>

        <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-sm text-purple-600">RevPAR</div>
            <div class="text-2xl font-bold text-purple-700">Rp {{ number_format($revpar, 0, ',', '.') }}</div>
        </div>

        <div class="bg-orange-50 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600">Total Revenue</div>
            <div class="text-2xl font-bold text-orange-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue Breakdown</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-sm text-gray-600">Room Revenue</div>
                <div class="text-xl font-bold text-blue-600">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-sm text-gray-600">F&B Revenue</div>
                <div class="text-xl font-bold text-green-600">Rp {{ number_format($fnbRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded">
                <div class="text-sm text-gray-600">Total</div>
                <div class="text-xl font-bold text-purple-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Today's Arrivals -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Today's Arrivals ({{ $arrivals->count() }})</h2>
            @forelse($arrivals as $stay)
            <div class="border-b py-2 last:border-0">
                <div class="font-semibold">{{ $stay->guest->full_name }}</div>
                <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }} • {{ $stay->actual_check_in ? $stay->actual_check_in->format('H:i') : '-' }}</div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No arrivals</p>
            @endforelse
        </div>

        <!-- Today's Departures -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Today's Departures ({{ $departures->count() }})</h2>
            @forelse($departures as $stay)
            <div class="border-b py-2 last:border-0">
                <div class="font-semibold">{{ $stay->guest->full_name }}</div>
                <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }} • {{ $stay->actual_check_out ? $stay->actual_check_out->format('H:i') : '-' }}</div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No departures</p>
            @endforelse
        </div>
    </div>

    <!-- Currently Occupied -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Currently Occupied Rooms ({{ $occupiedRooms->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Room</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Guest</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Check-in</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Check-out</th>
                        <th class="px-4 py-2 text-right text-sm font-semibold">Nights</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($occupiedRooms as $stay)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold">{{ $stay->hotelRoom->room_number }}</td>
                        <td class="px-4 py-3">{{ $stay->guest->full_name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $stay->check_in_date->format('d M') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $stay->check_out_date->format('d M') }}</td>
                        <td class="px-4 py-3 text-sm text-right">{{ $stay->nights }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No occupied rooms</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tomorrow's Expected -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expected Arrivals Tomorrow -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Expected Arrivals Tomorrow ({{ $expectedArrivals->count() }})</h2>
            @forelse($expectedArrivals as $stay)
            <div class="border-b py-2 last:border-0">
                <div class="font-semibold">{{ $stay->guest->full_name }}</div>
                <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }}</div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No expected arrivals</p>
            @endforelse
        </div>

        <!-- Expected Departures Tomorrow -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Expected Departures Tomorrow ({{ $expectedDepartures->count() }})</h2>
            @forelse($expectedDepartures as $stay)
            <div class="border-b py-2 last:border-0">
                <div class="font-semibold">{{ $stay->guest->full_name }}</div>
                <div class="text-sm text-gray-600">Room {{ $stay->hotelRoom->room_number }}</div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No expected departures</p>
            @endforelse
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none;
    }
}
</style>
</x-app-layout>
