<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Menu Baru</h1>
            <p class="text-gray-600">{{ $property->name }}</p>
        </div>
        <a href="{{ route('restaurant.menu.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('restaurant.menu.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Menu *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Contoh: Nasi Goreng Spesial">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                    <select name="category" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Kategori</option>
                        <option value="breakfast" {{ old('category') == 'breakfast' ? 'selected' : '' }}>Breakfast</option>
                        <option value="lunch" {{ old('category') == 'lunch' ? 'selected' : '' }}>Lunch</option>
                        <option value="dinner" {{ old('category') == 'dinner' ? 'selected' : '' }}>Dinner</option>
                        <option value="appetizer" {{ old('category') == 'appetizer' ? 'selected' : '' }}>Appetizer</option>
                        <option value="main_course" {{ old('category') == 'main_course' ? 'selected' : '' }}>Main Course</option>
                        <option value="dessert" {{ old('category') == 'dessert' ? 'selected' : '' }}>Dessert</option>
                        <option value="beverage" {{ old('category') == 'beverage' ? 'selected' : '' }}>Beverage</option>
                        <option value="snack" {{ old('category') == 'snack' ? 'selected' : '' }}>Snack</option>
                        <option value="alcohol" {{ old('category') == 'alcohol' ? 'selected' : '' }}>Alcohol</option>
                    </select>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                        <input type="number" name="price" value="{{ old('price') }}" required min="0" step="1000"
                            class="w-full pl-12 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="50000">
                    </div>
                </div>

                <!-- Availability -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="is_available" id="is_available" value="1" 
                            {{ old('is_available', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_available" class="ml-2 text-sm text-gray-700">Tersedia untuk dijual</label>
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Deskripsi menu (opsional)">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('restaurant.menu.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Simpan Menu
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
