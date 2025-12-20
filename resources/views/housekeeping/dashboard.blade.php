<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Housekeeping Dashboard</h1>
            <p class="text-gray-600">{{ $property->name ?? 'Property Name' }}</p>
        </div>
        <a href="{{ route('frontoffice.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
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

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600">Siap</div>
            <div class="text-2xl font-bold text-green-700">0</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-yellow-600">Kotor</div>
            <div class="text-2xl font-bold text-yellow-700">0</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-blue-600">Terisi</div>
            <div class="text-2xl font-bold text-blue-700">0</div>
        </div>
        <div class="bg-orange-50 rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-orange-600">Perbaikan</div>
            <div class="text-2xl font-bold text-orange-700">0</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="text-sm text-red-600">Rusak</div>
            <div class="text-2xl font-bold text-red-700">0</div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-center">
            <button onclick="selectAll()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                Pilih Semua
            </button>
            <button onclick="bulkClean()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Mark as Clean
            </button>
            <button onclick="bulkDirty()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition text-sm">
                Mark as Dirty
            </button>
            <button onclick="bulkMaintenance()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition text-sm">
                Set Maintenance
            </button>

            <div class="ml-auto flex gap-2">
                <select onchange="filterFloor(this.value)" class="border-gray-300 rounded-lg shadow-sm text-sm">
                    <option value="all">Semua Lantai</option>
                    <option value="1">Lantai 1</option>
                    <option value="2">Lantai 2</option>
                    <option value="3">Lantai 3</option>
                </select>

                <select onchange="filterStatus(this.value)" class="border-gray-300 rounded-lg shadow-sm text-sm">
                    <option value="all">Semua Status</option>
                    <option value="vacant_clean">Siap</option>
                    <option value="vacant_dirty">Kotor</option>
                    <option value="occupied">Terisi</option>
                    <option value="maintenance">Perbaikan</option>
                    <option value="out_of_order">Rusak</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Room List -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <input type="checkbox" id="select_all_checkbox" onchange="toggleSelectAll()" class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lantai</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terakhir Dibersihkan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="room_table_body">
                <!-- Sample Data - In production would come from backend -->
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <p class="text-lg">Belum ada data kamar</p>
                        <p class="text-sm text-gray-400 mt-2">Data kamar akan muncul setelah migrasi database selesai</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Keterangan Status:</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                <span>Siap (Vacant Clean)</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-yellow-500 rounded mr-2"></div>
                <span>Kotor (Vacant Dirty)</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                <span>Terisi (Occupied)</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-orange-500 rounded mr-2"></div>
                <span>Perbaikan (Maintenance)</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                <span>Rusak (Out of Order)</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-gray-500 rounded mr-2"></div>
                <span>Diblokir (Blocked)</span>
            </div>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-xl font-bold text-gray-800">Ubah Status Kamar</h2>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kamar</label>
                    <div id="modal_room_number" class="text-lg font-bold text-gray-800"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru *</label>
                    <select id="new_status" class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="vacant_clean">Siap (Vacant Clean)</option>
                        <option value="vacant_dirty">Kotor (Vacant Dirty)</option>
                        <option value="maintenance">Perbaikan (Maintenance)</option>
                        <option value="out_of_order">Rusak (Out of Order)</option>
                        <option value="blocked">Diblokir (Blocked)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Petugas</label>
                    <select id="assign_staff" class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="">-- Pilih Petugas --</option>
                        <option value="1">Staff 1</option>
                        <option value="2">Staff 2</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea id="status_notes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeStatusModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
                    Batal
                </button>
                <button onclick="saveStatus()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedRooms = [];
let currentRoomId = null;

function toggleSelectAll() {
    const checkbox = document.getElementById('select_all_checkbox');
    const allCheckboxes = document.querySelectorAll('.room-checkbox');

    allCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            if (!selectedRooms.includes(cb.value)) {
                selectedRooms.push(cb.value);
            }
        } else {
            selectedRooms = [];
        }
    });
}

function selectAll() {
    document.getElementById('select_all_checkbox').checked = true;
    toggleSelectAll();
}

function bulkClean() {
    if (selectedRooms.length === 0) {
        alert('Pilih minimal 1 kamar');
        return;
    }

    if (confirm(`Mark ${selectedRooms.length} kamar as Clean?`)) {
        alert('Fitur bulk clean akan segera tersedia!\n\nKamar yang dipilih: ' + selectedRooms.length);
        selectedRooms = [];
        document.getElementById('select_all_checkbox').checked = false;
    }
}

function bulkDirty() {
    if (selectedRooms.length === 0) {
        alert('Pilih minimal 1 kamar');
        return;
    }

    if (confirm(`Mark ${selectedRooms.length} kamar as Dirty?`)) {
        alert('Fitur bulk dirty akan segera tersedia!');
        selectedRooms = [];
        document.getElementById('select_all_checkbox').checked = false;
    }
}

function bulkMaintenance() {
    if (selectedRooms.length === 0) {
        alert('Pilih minimal 1 kamar');
        return;
    }

    if (confirm(`Set ${selectedRooms.length} kamar ke Maintenance?`)) {
        alert('Fitur bulk maintenance akan segera tersedia!');
        selectedRooms = [];
        document.getElementById('select_all_checkbox').checked = false;
    }
}

function filterFloor(floor) {
    console.log('Filter by floor:', floor);
    // In production, this would filter the table
}

function filterStatus(status) {
    console.log('Filter by status:', status);
    // In production, this would filter the table
}

function showChangeStatus(roomId, roomNumber) {
    currentRoomId = roomId;
    document.getElementById('modal_room_number').textContent = 'Kamar ' + roomNumber;
    document.getElementById('statusModal').classList.remove('hidden');
    document.getElementById('statusModal').classList.add('flex');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    document.getElementById('statusModal').classList.remove('flex');
    currentRoomId = null;
}

function saveStatus() {
    const newStatus = document.getElementById('new_status').value;
    const assignStaff = document.getElementById('assign_staff').value;
    const notes = document.getElementById('status_notes').value;

    if (!newStatus) {
        alert('Pilih status baru');
        return;
    }

    alert('Fitur update status akan segera tersedia!\n\nRoom ID: ' + currentRoomId + '\nNew Status: ' + newStatus);
    closeStatusModal();
}

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});
</script>
</x-app-layout>
