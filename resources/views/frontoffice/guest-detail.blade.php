<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Guest Profile</h1>
            <p class="text-gray-600">{{ $guest->full_name }}</p>
        </div>
        <a href="{{ route('frontoffice.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Guest Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Tamu</h2>

                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-600">Nama Lengkap</label>
                        <div class="font-semibold text-gray-800">{{ $guest->full_name }}</div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Email</label>
                        <div class="font-semibold text-gray-800">{{ $guest->email ?? '-' }}</div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Telepon</label>
                        <div class="font-semibold text-gray-800">{{ $guest->phone }}</div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">ID Type</label>
                        <div class="font-semibold text-gray-800">{{ strtoupper($guest->id_type) }}</div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">ID Number</label>
                        <div class="font-semibold text-gray-800">{{ $guest->id_number }}</div>
                    </div>

                    @if($guest->address)
                    <div>
                        <label class="text-sm text-gray-600">Alamat</label>
                        <div class="font-semibold text-gray-800">{{ $guest->address }}</div>
                    </div>
                    @endif

                    @if($guest->city)
                    <div>
                        <label class="text-sm text-gray-600">Kota</label>
                        <div class="font-semibold text-gray-800">{{ $guest->city }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Statistik</h2>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Menginap:</span>
                        <span class="font-bold text-blue-600">{{ $guest->roomStays->count() }} kali</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Spending:</span>
                        <span class="font-bold text-green-600">Rp {{ number_format($guest->roomStays->sum('total_amount'), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stay History & F&B Orders -->
        <div class="lg:col-span-2">
            <!-- Stay History -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Riwayat Menginap</h2>

                @forelse($guest->roomStays as $stay)
                <div class="border rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-lg text-gray-800">
                                Room {{ $stay->hotelRoom->room_number }} - {{ $stay->hotelRoom->roomType->name }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $stay->check_in_date->format('d M Y') }} - {{ $stay->check_out_date->format('d M Y') }}
                                ({{ $stay->nights }} malam)
                            </div>
                            <div class="mt-2">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($stay->status === 'checked_in') bg-blue-100 text-blue-800
                                    @elseif($stay->status === 'checked_out') bg-green-100 text-green-800
                                    @elseif($stay->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($stay->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-blue-600">
                                Rp {{ number_format($stay->total_amount, 0, ',', '.') }}
                            </div>
                            @if($stay->status === 'checked_out')
                            <a href="{{ route('frontoffice.invoice', $stay) }}" target="_blank"
                               class="text-sm text-blue-600 hover:underline">
                                View Invoice
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">Belum ada riwayat menginap</p>
                @endforelse
            </div>

            <!-- F&B Orders History -->
            @if($guest->fnbOrders->count() > 0)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Riwayat F&B Orders</h2>

                @foreach($guest->fnbOrders as $order)
                <div class="border rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="font-bold text-gray-800">#{{ $order->order_number }}</div>
                            <div class="text-sm text-gray-600">{{ $order->order_time->format('d M Y, H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-blue-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ $order->items->count() }} items</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded p-2 text-sm">
                        @foreach($order->items as $item)
                        <div class="py-1">{{ $item->quantity }}x {{ $item->menuItem->name }}</div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
