<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Kamar Hotel</h1>
            <p class="text-gray-600">Kelola semua kamar hotel untuk setiap properti</p>
        </div>
        <a href="{{ route('admin.hotel-rooms.create', ['property_id' => $propertyId]) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Kamar
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <div class="flex">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
        <div class="flex">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Statistics (only show if property selected) -->
    @if($propertyId && !empty($stats))
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Kamar</div>
            <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600">Siap</div>
            <div class="text-2xl font-bold text-green-700">{{ $stats['vacant_clean'] }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-yellow-600">Kotor</div>
            <div class="text-2xl font-bold text-yellow-700">{{ $stats['vacant_dirty'] }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-blue-600">Terisi</div>
            <div class="text-2xl font-bold text-blue-700">{{ $stats['occupied'] }}</div>
        </div>
        <div class="bg-orange-50 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600">Perbaikan</div>
            <div class="text-2xl font-bold text-orange-700">{{ $stats['maintenance'] }}</div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.hotel-rooms.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Property Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Properti</label>
                <select name="property_id" onchange="this.form.submit()" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <option value="">-- Pilih Properti --</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" {{ $propertyId == $property->id ? 'selected' : '' }}>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <option value="all">Semua Status</option>
                    <option value="vacant_clean" {{ request('status') == 'vacant_clean' ? 'selected' : '' }}>Siap</option>
                    <option value="vacant_dirty" {{ request('status') == 'vacant_dirty' ? 'selected' : '' }}>Kotor</option>
                    <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Terisi</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Perbaikan</option>
                    <option value="out_of_order" {{ request('status') == 'out_of_order' ? 'selected' : '' }}>Rusak</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Diblokir</option>
                </select>
            </div>

            <!-- Floor Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
                <select name="floor" onchange="this.form.submit()" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <option value="all">Semua Lantai</option>
                    @foreach($floors as $floor)
                        <option value="{{ $floor }}" {{ request('floor') == $floor ? 'selected' : '' }}>
                            Lantai {{ $floor }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nomor Kamar</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Contoh: 101" class="w-full border-gray-300 rounded-lg shadow-sm">
            </div>

            <!-- Search Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Rooms Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($rooms->count() > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properti</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lantai</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kapasitas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Smoking</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas HK</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($rooms as $room)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $room->property->name }}</td>
                    <td class="px-4 py-3 text-sm font-semibold">{{ $room->room_number }}</td>
                    <td class="px-4 py-3 text-sm">{{ $room->floor ?: '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $room->roomType->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $room->capacity }} pax</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-block px-2 py-1 text-xs rounded
                            @if($room->status === 'vacant_clean') bg-green-100 text-green-800
                            @elseif($room->status === 'vacant_dirty') bg-yellow-100 text-yellow-800
                            @elseif($room->status === 'occupied') bg-blue-100 text-blue-800
                            @elseif($room->status === 'maintenance') bg-orange-100 text-orange-800
                            @elseif($room->status === 'out_of_order') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $room->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if($room->is_smoking)
                            <span class="text-orange-600">🚬 Smoking</span>
                        @else
                            <span class="text-green-600">🚭 Non-Smoking</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">{{ $room->assignedHousekeeper->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.hotel-rooms.edit', $room) }}"
                               class="text-blue-600 hover:text-blue-800" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('admin.hotel-rooms.destroy', $room) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus kamar {{ $room->room_number }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t">
            {{ $rooms->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            @if(!$propertyId)
                <p class="text-gray-600 text-lg mb-2">Silakan pilih properti terlebih dahulu</p>
                <p class="text-gray-400 text-sm">Pilih properti dari dropdown di atas untuk melihat daftar kamar</p>
            @else
                <p class="text-gray-600 text-lg mb-2">Belum ada kamar terdaftar</p>
                <p class="text-gray-400 text-sm mb-4">Mulai tambahkan kamar untuk properti ini</p>
                <a href="{{ route('admin.hotel-rooms.create', ['property_id' => $propertyId]) }}"
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                    Tambah Kamar Pertama
                </a>
            @endif
        </div>
        @endif
    </div>
</div>
</x-app-layout>
