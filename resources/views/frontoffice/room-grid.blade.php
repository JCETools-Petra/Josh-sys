<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Room Grid - {{ $property->name }}</h1>
            <p class="text-gray-600">Status Kamar Real-Time</p>
        </div>
        <a href="{{ route('frontoffice.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Legend -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Keterangan:</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                <span class="text-sm">Siap</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-yellow-500 rounded mr-2"></div>
                <span class="text-sm">Kotor</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                <span class="text-sm">Terisi</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-orange-500 rounded mr-2"></div>
                <span class="text-sm">Perbaikan</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                <span class="text-sm">Rusak</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-gray-500 rounded mr-2"></div>
                <span class="text-sm">Diblokir</span>
            </div>
        </div>
    </div>

    <!-- Room Grid -->
    @php
        $roomsByFloor = $rooms->groupBy('floor');
    @endphp

    @foreach($roomsByFloor as $floor => $floorRooms)
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-3">
            @if($floor)
                Lantai {{ $floor }}
            @else
                Tanpa Lantai
            @endif
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($floorRooms as $room)
            @php
                $statusColors = [
                    'vacant_clean' => 'bg-green-500 hover:bg-green-600',
                    'vacant_dirty' => 'bg-yellow-500 hover:bg-yellow-600',
                    'occupied' => 'bg-blue-500 hover:bg-blue-600',
                    'maintenance' => 'bg-orange-500 hover:bg-orange-600',
                    'out_of_order' => 'bg-red-500 hover:bg-red-600',
                    'blocked' => 'bg-gray-500 hover:bg-gray-600',
                ];
                $colorClass = $statusColors[$room->status] ?? 'bg-gray-500 hover:bg-gray-600';
            @endphp

            <div class="relative">
                <div class="{{ $colorClass }} text-white rounded-lg shadow-lg p-4 cursor-pointer transition-all hover:scale-105"
                     onclick="showRoomDetail({{ json_encode($room) }})">
                    <!-- Room Number -->
                    <div class="text-center">
                        <div class="text-3xl font-bold">{{ $room->room_number }}</div>
                        <div class="text-xs opacity-90">{{ $room->roomType->name ?? 'Standard' }}</div>
                    </div>

                    <!-- Capacity -->
                    <div class="mt-2 text-xs text-center opacity-90">
                        <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                        </svg>
                        {{ $room->capacity }} pax
                    </div>

                    @if($room->is_smoking)
                    <div class="absolute top-2 right-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" title="Smoking">
                            <path d="M12 8a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1zM10 10a1 1 0 011-1h4a1 1 0 110 2h-4a1 1 0 01-1-1z"></path>
                        </svg>
                    </div>
                    @endif

                    <!-- Status Badge -->
                    <div class="mt-3 text-center">
                        <span class="inline-block bg-white bg-opacity-30 text-xs px-2 py-1 rounded">
                            {{ $room->status_label }}
                        </span>
                    </div>

                    <!-- Guest Name if Occupied -->
                    @if($room->status === 'occupied' && $room->currentStay)
                    <div class="mt-2 text-xs text-center truncate" title="{{ $room->currentStay->guest->full_name }}">
                        {{ $room->currentStay->guest->full_name }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @if($rooms->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <p class="text-gray-600 text-lg">Belum ada kamar terdaftar</p>
    </div>
    @endif
</div>

<!-- Room Detail Modal -->
<div id="roomDetailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800" id="modal_room_number"></h2>
                    <p class="text-gray-600" id="modal_room_type"></p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Status</label>
                        <div class="font-semibold" id="modal_status"></div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Kapasitas</label>
                        <div class="font-semibold" id="modal_capacity"></div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Lantai</label>
                        <div class="font-semibold" id="modal_floor"></div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Tipe Ruangan</label>
                        <div class="font-semibold" id="modal_smoking"></div>
                    </div>
                </div>

                <div id="guest_info" class="hidden border-t pt-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Informasi Tamu</h3>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm text-gray-600">Nama Tamu</label>
                                <div class="font-semibold" id="guest_name"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-gray-600">Check-In</label>
                                    <div class="font-semibold" id="checkin_date"></div>
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Check-Out</label>
                                    <div class="font-semibold" id="checkout_date"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="cleaning_info" class="border-t pt-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Info Housekeeping</h3>
                    <div class="text-sm text-gray-600">
                        <p>Terakhir dibersihkan: <span class="font-semibold" id="last_cleaned"></span></p>
                        <p>Petugas: <span class="font-semibold" id="hk_assigned"></span></p>
                    </div>
                </div>

                <div id="features_info" class="border-t pt-4 hidden">
                    <h3 class="font-semibold text-gray-800 mb-2">Fasilitas Kamar</h3>
                    <div id="features_list" class="flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
                    Tutup
                </button>
                <a id="checkin_btn" href="#" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition hidden">
                    Check-In
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function showRoomDetail(room) {
    const modal = document.getElementById('roomDetailModal');

    // Basic info
    document.getElementById('modal_room_number').textContent = 'Kamar ' + room.room_number;
    document.getElementById('modal_room_type').textContent = room.room_type?.name || 'Standard';
    document.getElementById('modal_status').textContent = room.status_label;
    document.getElementById('modal_capacity').textContent = room.capacity + ' orang';
    document.getElementById('modal_floor').textContent = room.floor || '-';
    document.getElementById('modal_smoking').textContent = room.is_smoking ? 'Smoking' : 'Non-Smoking';

    // Guest info (if occupied)
    const guestInfo = document.getElementById('guest_info');
    if (room.status === 'occupied' && room.current_stay) {
        guestInfo.classList.remove('hidden');
        document.getElementById('guest_name').textContent = room.current_stay.guest?.full_name || '-';
        document.getElementById('checkin_date').textContent = formatDate(room.current_stay.check_in_date);
        document.getElementById('checkout_date').textContent = formatDate(room.current_stay.check_out_date);
    } else {
        guestInfo.classList.add('hidden');
    }

    // Cleaning info
    document.getElementById('last_cleaned').textContent = room.last_cleaned_at ? formatDate(room.last_cleaned_at) : 'Belum pernah';
    document.getElementById('hk_assigned').textContent = room.assigned_housekeeper?.name || 'Belum ditentukan';

    // Features
    if (room.features && room.features.length > 0) {
        document.getElementById('features_info').classList.remove('hidden');
        const featuresList = document.getElementById('features_list');
        featuresList.innerHTML = '';
        room.features.forEach(feature => {
            const span = document.createElement('span');
            span.className = 'bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded';
            span.textContent = feature;
            featuresList.appendChild(span);
        });
    } else {
        document.getElementById('features_info').classList.add('hidden');
    }

    // Check-in button (only show for available rooms)
    const checkinBtn = document.getElementById('checkin_btn');
    if (room.status === 'vacant_clean') {
        checkinBtn.classList.remove('hidden');
        checkinBtn.href = '{{ route("frontoffice.index") }}?room_id=' + room.id;
    } else {
        checkinBtn.classList.add('hidden');
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('roomDetailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('id-ID', options);
}

// Close modal when clicking outside
document.getElementById('roomDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
</x-app-layout>
