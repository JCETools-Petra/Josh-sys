<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Add New Menu Item</h1>
        <p class="text-gray-600">{{ $property->name }}</p>
    </div>

    <div class="max-w-2xl bg-white rounded-lg shadow-lg p-6">
        <form method="POST" action="{{ route('restaurant.menu.store') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="e.g., Nasi Goreng Special">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category" id="category" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Category</option>
                    <option value="breakfast" {{ old('category') === 'breakfast' ? 'selected' : '' }}>🌅 Breakfast</option>
                    <option value="lunch" {{ old('category') === 'lunch' ? 'selected' : '' }}>🍱 Lunch</option>
                    <option value="dinner" {{ old('category') === 'dinner' ? 'selected' : '' }}>🍽️ Dinner</option>
                    <option value="appetizer" {{ old('category') === 'appetizer' ? 'selected' : '' }}>🥗 Appetizer</option>
                    <option value="main_course" {{ old('category') === 'main_course' ? 'selected' : '' }}>🍖 Main Course</option>
                    <option value="dessert" {{ old('category') === 'dessert' ? 'selected' : '' }}>🍰 Dessert</option>
                    <option value="beverage" {{ old('category') === 'beverage' ? 'selected' : '' }}>☕ Beverage</option>
                    <option value="snack" {{ old('category') === 'snack' ? 'selected' : '' }}>🍪 Snack</option>
                    <option value="alcohol" {{ old('category') === 'alcohol' ? 'selected' : '' }}>🍷 Alcohol</option>
                </select>
                @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Price -->
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (Rp) *</label>
                <input type="number" name="price" id="price" value="{{ old('price') }}" required min="0" step="1000"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="25000">
                @error('price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Brief description of the dish">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Availability -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Item is available for order</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Create Menu Item
                </button>
                <a href="{{ route('restaurant.menu.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
