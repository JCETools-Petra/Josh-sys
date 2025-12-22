<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Menu Management</h1>
            <p class="text-gray-600">{{ $property->name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('restaurant.menu.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                + Add Menu Item
            </a>
            <a href="{{ route('restaurant.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Back to Restaurant
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
    </div>
    @endif

    <!-- Filter by Category -->
    <div class="mb-4 bg-white rounded-lg shadow p-4">
        <div class="flex space-x-2 overflow-x-auto">
            <a href="{{ route('restaurant.menu.index') }}" class="px-4 py-2 rounded {{ !request('category') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                All
            </a>
            @foreach(['breakfast' => '🌅 Breakfast', 'lunch' => '🍱 Lunch', 'dinner' => '🍽️ Dinner', 'beverage' => '☕ Beverage', 'snack' => '🍪 Snack', 'dessert' => '🍰 Dessert'] as $cat => $label)
            <a href="{{ route('restaurant.menu.index', ['category' => $cat]) }}" class="px-4 py-2 rounded whitespace-nowrap {{ request('category') === $cat ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Menu Items Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($menuItems as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                        @if($item->description)
                        <div class="text-sm text-gray-500">{{ Str::limit($item->description, 50) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($item->category === 'breakfast') bg-yellow-100 text-yellow-800
                            @elseif($item->category === 'lunch') bg-orange-100 text-orange-800
                            @elseif($item->category === 'dinner') bg-red-100 text-red-800
                            @elseif($item->category === 'beverage') bg-blue-100 text-blue-800
                            @elseif($item->category === 'snack') bg-green-100 text-green-800
                            @else bg-purple-100 text-purple-800
                            @endif">
                            {{ ucfirst($item->category) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right font-semibold text-gray-900">
                        Rp {{ number_format($item->price, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="toggleAvailability({{ $item->id }}, {{ $item->is_available ? 'true' : 'false' }})"
                                id="toggle-btn-{{ $item->id }}"
                                class="px-3 py-1 text-xs font-semibold rounded-full {{ $item->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <span id="toggle-text-{{ $item->id }}">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-center text-sm font-medium">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('restaurant.menu.edit', $item) }}" class="text-blue-600 hover:text-blue-900">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('restaurant.menu.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        No menu items found. <a href="{{ route('restaurant.menu.create') }}" class="text-blue-600 hover:underline">Add your first item</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $menuItems->links() }}
    </div>
</div>

<script>
    async function toggleAvailability(itemId, currentStatus) {
        try {
            const response = await fetch(`/restaurant/menu/${itemId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                const btn = document.getElementById(`toggle-btn-${itemId}`);
                const text = document.getElementById(`toggle-text-${itemId}`);

                if (data.is_available) {
                    btn.className = 'px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
                    text.textContent = 'Available';
                } else {
                    btn.className = 'px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800';
                    text.textContent = 'Unavailable';
                }
            }
        } catch (error) {
            console.error('Error toggling availability:', error);
            alert('Failed to update availability');
        }
    }
</script>
</x-app-layout>
