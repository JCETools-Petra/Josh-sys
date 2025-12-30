<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Catat Barang Temuan Baru</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="{{ route('housekeeping.lost-found.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Item Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nama Barang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="item_name" required
                        value="{{ old('item_name') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Contoh: Handphone Samsung Galaxy">
                    @error('item_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Kategori</option>
                        <option value="electronics" {{ old('category') === 'electronics' ? 'selected' : '' }}>Elektronik</option>
                        <option value="clothing" {{ old('category') === 'clothing' ? 'selected' : '' }}>Pakaian</option>
                        <option value="documents" {{ old('category') === 'documents' ? 'selected' : '' }}>Dokumen</option>
                        <option value="jewelry" {{ old('category') === 'jewelry' ? 'selected' : '' }}>Perhiasan</option>
                        <option value="accessories" {{ old('category') === 'accessories' ? 'selected' : '' }}>Aksesoris</option>
                        <option value="others" {{ old('category') === 'others' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('category')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Deskripsi <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" required rows="3"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Deskripsi detail barang yang ditemukan...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Warna
                    </label>
                    <input type="text" name="color"
                        value="{{ old('color') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Contoh: Hitam">
                    @error('color')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Brand -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Brand/Merek
                    </label>
                    <input type="text" name="brand"
                        value="{{ old('brand') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Contoh: Samsung">
                    @error('brand')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Room -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Kamar (jika ditemukan di kamar)
                    </label>
                    <select name="hotel_room_id"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tidak di kamar</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('hotel_room_id') == $room->id ? 'selected' : '' }}>
                                Kamar {{ $room->room_number }} - Lantai {{ $room->floor }}
                            </option>
                        @endforeach
                    </select>
                    @error('hotel_room_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location Found -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Lokasi Ditemukan
                    </label>
                    <input type="text" name="location_found"
                        value="{{ old('location_found') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Contoh: Lobby, Restaurant, dll">
                    @error('location_found')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Found -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tanggal Ditemukan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date_found" required
                        value="{{ old('date_found', date('Y-m-d')) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('date_found')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Storage Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Lokasi Penyimpanan
                    </label>
                    <input type="text" name="storage_location"
                        value="{{ old('storage_location') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Contoh: Lemari Lost & Found Cabinet A1">
                    @error('storage_location')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Catatan Tambahan
                    </label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Catatan tambahan jika ada...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Simpan Barang Temuan
                </button>
                <a href="{{ route('housekeeping.lost-found.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
