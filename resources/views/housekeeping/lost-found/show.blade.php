<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
            <a href="{{ route('housekeeping.lost-found.index') }}" class="hover:text-blue-600">Lost & Found</a>
            <span>/</span>
            <span>{{ $lostAndFound->item_number }}</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Detail Barang Temuan</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ $lostAndFound->property->name }}</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $lostAndFound->item_name }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nomor: {{ $lostAndFound->item_number }}</p>
                    </div>
                    <div>
                        @if($lostAndFound->status === 'stored')
                            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded">Disimpan</span>
                        @elseif($lostAndFound->status === 'claimed')
                            <span class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded">Diklaim</span>
                        @elseif($lostAndFound->status === 'disposed')
                            <span class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded">Dibuang</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori</h3>
                        <p class="text-gray-900 dark:text-white">{{ ucfirst($lostAndFound->category) }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->description }}</p>
                    </div>

                    @if($lostAndFound->color)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Warna</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->color }}</p>
                    </div>
                    @endif

                    @if($lostAndFound->brand)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Brand/Merek</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->brand }}</p>
                    </div>
                    @endif

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Lokasi Ditemukan</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->location_found }}</p>
                        @if($lostAndFound->hotelRoom)
                            <p class="text-sm text-gray-600 dark:text-gray-400">Kamar {{ $lostAndFound->hotelRoom->room_number }}</p>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Ditemukan</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->date_found->format('d F Y') }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $lostAndFound->date_found->diffForHumans() }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Ditemukan Oleh</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->foundBy->name }}</p>
                    </div>

                    @if($lostAndFound->storage_location)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Lokasi Penyimpanan</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->storage_location }}</p>
                    </div>
                    @endif

                    @if($lostAndFound->notes)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan</h3>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if($lostAndFound->status === 'claimed')
            <!-- Claim Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Informasi Klaim</h3>

                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Diklaim Oleh</h4>
                        @if($lostAndFound->claimedByGuest)
                            <p class="text-gray-900 dark:text-white">{{ $lostAndFound->claimedByGuest->full_name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $lostAndFound->claimedByGuest->phone }}</p>
                        @else
                            <p class="text-gray-900 dark:text-white">{{ $lostAndFound->claimed_by_name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $lostAndFound->claimed_by_phone }}</p>
                        @endif
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Diklaim</h4>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->claimed_at->format('d F Y H:i') }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Diproses Oleh</h4>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->releasedBy->name }}</p>
                    </div>

                    @if($lostAndFound->claim_notes)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan Klaim</h4>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->claim_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Actions -->
            @if($lostAndFound->status === 'stored')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Aksi</h3>

                <div class="space-y-3">
                    <a href="{{ route('housekeeping.lost-found.claim-form', $lostAndFound) }}"
                        class="block w-full bg-green-600 hover:bg-green-700 text-white text-center px-4 py-2 rounded-lg">
                        Proses Klaim
                    </a>

                    <form action="{{ route('housekeeping.lost-found.dispose', $lostAndFound) }}" method="POST"
                        onsubmit="return confirm('Yakin ingin membuang barang ini?')">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            Buang Barang
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Disposal Info -->
            @if($lostAndFound->status === 'stored' && $lostAndFound->disposal_date)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Informasi Pembuangan</h3>

                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Batas Waktu Pembuangan</p>
                    <p class="text-gray-900 dark:text-white font-semibold">{{ $lostAndFound->disposal_date->format('d F Y') }}</p>
                    @if($lostAndFound->disposal_date->isPast())
                        <p class="text-sm text-red-600 mt-1">Sudah lewat {{ $lostAndFound->disposal_date->diffForHumans() }}</p>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $lostAndFound->disposal_date->diffForHumans() }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Guest Info -->
            @if($lostAndFound->guest)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Tamu Terkait</h3>

                <div class="space-y-2">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nama</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ $lostAndFound->guest->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Telepon</p>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->guest->phone }}</p>
                    </div>
                    @if($lostAndFound->guest->email)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->guest->email }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
