<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Lost & Found</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
        </div>
        <a href="{{ route('housekeeping.lost-found.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            + Catat Barang Baru
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700 dark:text-green-400">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded">
        <p class="text-red-700 dark:text-red-400">{{ session('error') }}</p>
    </div>
    @endif

    @if(session('info'))
    <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-blue-700 dark:text-blue-400">{{ session('info') }}</p>
    </div>
    @endif

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-blue-600 dark:text-blue-400">Disimpan</div>
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-500">{{ $stats['stored'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600 dark:text-green-400">Diklaim</div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-500">{{ $stats['claimed'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="text-sm text-red-600 dark:text-red-400">Siap Dibuang (90+ hari)</div>
            <div class="text-2xl font-bold text-red-700 dark:text-red-500">{{ $stats['ready_disposal'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('housekeeping.lost-found.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama barang, nomor, deskripsi..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="stored" {{ request('status') === 'stored' ? 'selected' : '' }}>Disimpan</option>
                    <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Diklaim</option>
                    <option value="disposed" {{ request('status') === 'disposed' ? 'selected' : '' }}>Dibuang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                <select name="category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Kategori</option>
                    <option value="electronics" {{ request('category') === 'electronics' ? 'selected' : '' }}>Elektronik</option>
                    <option value="clothing" {{ request('category') === 'clothing' ? 'selected' : '' }}>Pakaian</option>
                    <option value="documents" {{ request('category') === 'documents' ? 'selected' : '' }}>Dokumen</option>
                    <option value="jewelry" {{ request('category') === 'jewelry' ? 'selected' : '' }}>Perhiasan</option>
                    <option value="accessories" {{ request('category') === 'accessories' ? 'selected' : '' }}>Aksesoris</option>
                    <option value="others" {{ request('category') === 'others' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Filter
                </button>
                <a href="{{ route('housekeeping.lost-found.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Reset
                </a>
            </div>
        </form>

        @if($stats['ready_disposal'] > 0)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <form action="{{ route('housekeeping.lost-found.bulk-dispose') }}" method="POST"
                onsubmit="return confirm('Yakin ingin membuang {{ $stats['ready_disposal'] }} barang yang sudah lebih dari 90 hari?')">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    Buang Semua Barang 90+ Hari ({{ $stats['ready_disposal'] }} item)
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Items List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nomor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Barang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Lokasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ $item->item_number }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->item_name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($item->description, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ ucfirst($item->category) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $item->location_found }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $item->date_found->format('d M Y') }}
                        @if($item->status === 'stored' && $item->disposal_date && $item->disposal_date->isPast())
                            <div class="text-xs text-red-600">Lewat {{ $item->disposal_date->diffForHumans() }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item->status === 'stored')
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Disimpan</span>
                        @elseif($item->status === 'claimed')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Diklaim</span>
                        @elseif($item->status === 'disposed')
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Dibuang</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex gap-2">
                            <a href="{{ route('housekeeping.lost-found.show', $item) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                            @if($item->status === 'stored')
                                <a href="{{ route('housekeeping.lost-found.claim-form', $item) }}" class="text-green-600 hover:text-green-900">Klaim</a>
                                <form action="{{ route('housekeeping.lost-found.dispose', $item) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Yakin ingin membuang barang ini?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900">Buang</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada barang ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
</x-app-layout>
