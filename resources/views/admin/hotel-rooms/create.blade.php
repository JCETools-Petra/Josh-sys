<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Kamar Hotel Baru</h1>
            <p class="text-gray-600">Tambahkan kamar baru untuk properti</p>
        </div>
        <a href="{{ route('admin.hotel-rooms.index', ['property_id' => $propertyId]) }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

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

    <form action="{{ route('admin.hotel-rooms.store') }}" method="POST" class="bg-white rounded-lg shadow-lg">
        @csrf

        <div class="p-6 space-y-6">
            <!-- Property & Room Info -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">1. Informasi Properti & Kamar</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Properti *</label>
                        <select name="property_id" id="property_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Properti --</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ old('property_id', $propertyId) == $property->id ? 'selected' : '' }}>
                                    {{ $property->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('property_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kamar *</label>
                        <input type="text" name="room_number" required
                            value="{{ old('room_number') }}"
                            placeholder="Contoh: 101"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('room_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lantai</label>
                        <input type="text" name="floor"
                            value="{{ old('floor') }}"
                            placeholder="Contoh: 1, 2, 3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('floor')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Room Type & Capacity -->
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <h3 class="text-lg font-semibold text-green-900 mb-4">2. Tipe Kamar & Kapasitas</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kamar *</label>
                        <select name="room_type_id" id="room_type_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">-- Pilih Tipe Kamar --</option>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">* Pilih properti terlebih dahulu</p>
                        @error('room_type_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas *</label>
                        <input type="number" name="capacity" required min="1" value="{{ old('capacity', 2) }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        <p class="mt-1 text-xs text-gray-500">Jumlah orang maksimal</p>
                        @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Ruangan</label>
                        <div class="flex items-center space-x-4 mt-3">
                            <label class="flex items-center">
                                <input type="radio" name="is_smoking" value="0" {{ old('is_smoking', 0) == 0 ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <span class="ml-2 text-sm">🚭 Non-Smoking</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="is_smoking" value="1" {{ old('is_smoking') == 1 ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm">🚬 Smoking</span>
                            </label>
                        </div>
                        @error('is_smoking')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Room Status & Assignment -->
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">3. Status & Assignment</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Kamar *</label>
                        <select name="status" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            <option value="vacant_clean" {{ old('status', 'vacant_clean') == 'vacant_clean' ? 'selected' : '' }}>Siap (Vacant Clean)</option>
                            <option value="vacant_dirty" {{ old('status') == 'vacant_dirty' ? 'selected' : '' }}>Kotor (Vacant Dirty)</option>
                            <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Terisi (Occupied)</option>
                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Perbaikan (Maintenance)</option>
                            <option value="out_of_order" {{ old('status') == 'out_of_order' ? 'selected' : '' }}>Rusak (Out of Order)</option>
                            <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Diblokir (Blocked)</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign Petugas Housekeeping</label>
                        <select name="assigned_hk_user_id"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($housekeepers as $hk)
                                <option value="{{ $hk->id }}" {{ old('assigned_hk_user_id') == $hk->id ? 'selected' : '' }}>
                                    {{ $hk->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_hk_user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Features & Notes -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <h3 class="text-lg font-semibold text-yellow-900 mb-4">4. Fasilitas & Catatan</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fasilitas Kamar</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="WiFi" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">WiFi</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="TV" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">TV</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="AC" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">AC</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="Minibar" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">Minibar</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="Balcony" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">Balkon</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="Bathtub" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">Bathtub</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="Safe Box" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">Safe Box</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="features[]" value="Hair Dryer" class="rounded border-gray-300 text-yellow-600">
                                <span class="ml-2 text-sm">Hair Dryer</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                            placeholder="Catatan tambahan tentang kamar ini...">{{ old('notes') }}</textarea>
                        @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end space-x-3">
            <a href="{{ route('admin.hotel-rooms.index', ['property_id' => $propertyId]) }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition">
                Batal
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Simpan Kamar
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('property_id');
    const roomTypeSelect = document.getElementById('room_type_id');

    propertySelect.addEventListener('change', function() {
        const propertyId = this.value;

        if (!propertyId) {
            roomTypeSelect.innerHTML = '<option value="">-- Pilih Tipe Kamar --</option>';
            return;
        }

        // Fetch room types for selected property
        fetch(`/admin/hotel-rooms/room-types/${propertyId}`)
            .then(response => response.json())
            .then(data => {
                roomTypeSelect.innerHTML = '<option value="">-- Pilih Tipe Kamar --</option>';
                data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    roomTypeSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching room types:', error);
            });
    });
});
</script>
</x-app-layout>
