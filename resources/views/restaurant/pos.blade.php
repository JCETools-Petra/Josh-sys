<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Point of Sale (POS)</h1>
            <p class="text-gray-600">{{ $property->name ?? 'Restaurant' }}</p>
        </div>
        <a href="{{ route('restaurant.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Menu Selection (Left Side) -->
        <div class="lg:col-span-2">
            <!-- Order Type Selection -->
            <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
                <h3 class="font-semibold text-gray-800 mb-3">Tipe Order</h3>
                <div class="grid grid-cols-4 gap-2">
                    <button onclick="setOrderType('dine_in')" id="btn_dine_in" class="order-type-btn bg-blue-600 text-white px-4 py-3 rounded-lg transition hover:bg-blue-700 text-sm font-semibold">
                        🍽️ Dine-In
                    </button>
                    <button onclick="setOrderType('room_service')" id="btn_room_service" class="order-type-btn bg-gray-200 text-gray-700 px-4 py-3 rounded-lg transition hover:bg-gray-300 text-sm font-semibold">
                        🛎️ Room Service
                    </button>
                    <button onclick="setOrderType('takeaway')" id="btn_takeaway" class="order-type-btn bg-gray-200 text-gray-700 px-4 py-3 rounded-lg transition hover:bg-gray-300 text-sm font-semibold">
                        📦 Takeaway
                    </button>
                    <button onclick="setOrderType('delivery')" id="btn_delivery" class="order-type-btn bg-gray-200 text-gray-700 px-4 py-3 rounded-lg transition hover:bg-gray-300 text-sm font-semibold">
                        🚗 Delivery
                    </button>
                </div>

                <!-- Room Selection (Only for Room Service) -->
                <div id="room_selection" class="mt-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kamar *</label>
                    <select id="room_select" class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($occupiedRooms as $room)
                            <option value="{{ $room->id }}">
                                Room {{ $room->room_number }} - {{ $room->currentStay->guest->full_name ?? 'Guest' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Table Selection (Only for Dine-In) -->
                <div id="table_selection" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Meja (Optional)</label>
                    <input type="text" id="table_number" placeholder="Contoh: Meja 5" class="w-full border-gray-300 rounded-lg shadow-sm">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
                <h3 class="font-semibold text-gray-800 mb-3">Kategori</h3>
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterCategory('all')" class="category-btn bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition hover:bg-blue-700">
                        Semua
                    </button>
                    <button onclick="filterCategory('breakfast')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition hover:bg-gray-300">
                        Breakfast
                    </button>
                    <button onclick="filterCategory('lunch')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition hover:bg-gray-300">
                        Lunch
                    </button>
                    <button onclick="filterCategory('dinner')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition hover:bg-gray-300">
                        Dinner
                    </button>
                    <button onclick="filterCategory('beverage')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition hover:bg-gray-300">
                        Minuman
                    </button>
                    <button onclick="filterCategory('snack')" class="category-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition hover:bg-gray-300">
                        Snack
                    </button>
                </div>
            </div>

            <!-- Menu Items Grid -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Menu</h3>

                <div id="menu_items_grid" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <!-- Menu items will be loaded here -->
                    <div class="text-center py-12 col-span-full text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <p>Belum ada menu item</p>
                        <p class="text-sm text-gray-400 mt-2">
                            <a href="{{ route('restaurant.menu.create') }}" class="text-blue-600 hover:underline">Tambahkan menu pertama</a> melalui Menu Management
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Cart (Right Side) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-4 sticky top-4">
                <h3 class="font-semibold text-gray-800 mb-4">Order Saat Ini</h3>

                <!-- Cart Items -->
                <div id="cart_items" class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-sm">Keranjang kosong</p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold" id="subtotal_display">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Pajak (10%):</span>
                        <span id="tax_display">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Service (5%):</span>
                        <span id="service_display">Rp 0</span>
                    </div>
                    <div class="border-t pt-2 flex justify-between font-bold text-lg text-blue-600">
                        <span>Total:</span>
                        <span id="total_display">Rp 0</span>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Khusus</label>
                    <textarea id="special_instructions" rows="2" placeholder="Contoh: Tidak pedas, extra sambal"
                        class="w-full border-gray-300 rounded-lg shadow-sm text-sm"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 space-y-2">
                    <button onclick="clearCart()" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-3 rounded-lg transition font-semibold">
                        Batal / Clear
                    </button>
                    <button onclick="createOrder()" id="create_order_btn" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        Buat Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// State management
let orderType = 'dine_in';
let selectedRoom = null;
let cart = [];
let currentCategory = 'all';

// Menu items from backend
const menuItems = @json($menuItems->flatten());

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderMenuItems();
    updateCart();
});

function setOrderType(type) {
    orderType = type;

    // Update button styles
    document.querySelectorAll('.order-type-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    document.getElementById('btn_' + type).classList.remove('bg-gray-200', 'text-gray-700');
    document.getElementById('btn_' + type).classList.add('bg-blue-600', 'text-white');

    // Show/hide room selection
    if (type === 'room_service') {
        document.getElementById('room_selection').classList.remove('hidden');
        document.getElementById('table_selection').classList.add('hidden');
    } else if (type === 'dine_in') {
        document.getElementById('room_selection').classList.add('hidden');
        document.getElementById('table_selection').classList.remove('hidden');
    } else {
        document.getElementById('room_selection').classList.add('hidden');
        document.getElementById('table_selection').classList.add('hidden');
    }
}

function filterCategory(category) {
    currentCategory = category;

    // Update button styles
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    event.target.classList.remove('bg-gray-200', 'text-gray-700');
    event.target.classList.add('bg-blue-600', 'text-white');

    renderMenuItems();
}

function renderMenuItems() {
    const grid = document.getElementById('menu_items_grid');
    const items = currentCategory === 'all'
        ? menuItems
        : menuItems.filter(item => item.category === currentCategory);

    if (items.length === 0) {
        grid.innerHTML = `
            <div class="text-center py-12 col-span-full text-gray-500">
                <p>Tidak ada menu di kategori ini</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = items.map(item => `
        <div onclick="addToCart(${item.id})" class="bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg p-3 cursor-pointer transition">
            <div class="font-semibold text-gray-800 text-sm mb-1">${item.name}</div>
            <div class="text-blue-600 font-bold text-sm">Rp ${formatNumber(item.price)}</div>
            <div class="text-xs text-gray-500 mt-1">${item.is_available ? '✓ Tersedia' : '✗ Habis'}</div>
        </div>
    `).join('');
}

function addToCart(itemId) {
    const menuItem = menuItems.find(item => item.id === itemId);
    if (!menuItem || !menuItem.is_available) {
        alert('Item tidak tersedia');
        return;
    }

    const existingItem = cart.find(item => item.id === itemId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: menuItem.id,
            name: menuItem.name,
            price: parseFloat(menuItem.price),
            quantity: 1
        });
    }

    updateCart();
}

function updateCart() {
    const cartContainer = document.getElementById('cart_items');
    const createBtn = document.getElementById('create_order_btn');

    if (cart.length === 0) {
        cartContainer.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-sm">Keranjang kosong</p>
            </div>
        `;
        createBtn.disabled = true;
    } else {
        cartContainer.innerHTML = cart.map(item => `
            <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                <div class="flex-1">
                    <div class="font-semibold text-sm text-gray-800">${item.name}</div>
                    <div class="text-xs text-gray-600">Rp ${formatNumber(item.price)} x ${item.quantity}</div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="decreaseQty(${item.id})" class="bg-gray-300 hover:bg-gray-400 w-6 h-6 rounded text-sm">-</button>
                    <span class="font-semibold text-sm w-6 text-center">${item.quantity}</span>
                    <button onclick="increaseQty(${item.id})" class="bg-blue-600 hover:bg-blue-700 text-white w-6 h-6 rounded text-sm">+</button>
                    <button onclick="removeFromCart(${item.id})" class="text-red-600 hover:text-red-800 ml-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
        createBtn.disabled = false;
    }

    updateSummary();
}

function increaseQty(itemId) {
    const item = cart.find(i => i.id === itemId);
    if (item) {
        item.quantity++;
        updateCart();
    }
}

function decreaseQty(itemId) {
    const item = cart.find(i => i.id === itemId);
    if (item && item.quantity > 1) {
        item.quantity--;
        updateCart();
    }
}

function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCart();
}

function clearCart() {
    if (cart.length > 0 && !confirm('Yakin ingin menghapus semua item?')) {
        return;
    }
    cart = [];
    updateCart();
}

function updateSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.10;
    const service = subtotal * 0.05;
    const total = subtotal + tax + service;

    document.getElementById('subtotal_display').textContent = 'Rp ' + formatNumber(subtotal);
    document.getElementById('tax_display').textContent = 'Rp ' + formatNumber(tax);
    document.getElementById('service_display').textContent = 'Rp ' + formatNumber(service);
    document.getElementById('total_display').textContent = 'Rp ' + formatNumber(total);
}

async function createOrder() {
    if (cart.length === 0) {
        alert('Keranjang masih kosong');
        return;
    }

    const btn = document.getElementById('create_order_btn');
    const originalText = btn.textContent;

    // Prepare order data
    const orderData = {
        order_type: orderType,
        table_number: orderType === 'dine_in' ? document.getElementById('table_number').value : null,
        hotel_room_id: orderType === 'room_service' ? document.getElementById('room_select').value : null,
        special_instructions: document.getElementById('special_instructions').value,
        items: cart.map(item => ({
            menu_item_id: item.id,
            quantity: item.quantity
        }))
    };

    // Validate room selection for room service
    if (orderType === 'room_service' && !orderData.hotel_room_id) {
        alert('Silakan pilih kamar terlebih dahulu');
        return;
    }

    try {
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        const response = await fetch('{{ route("restaurant.orders.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        const result = await response.json();

        if (result.success) {
            alert('✓ Order berhasil dibuat!\n\nOrder Number: ' + result.order.order_number + '\nTotal: Rp ' + formatNumber(result.order.total_amount));

            // Clear cart and reset form
            cart = [];
            updateCart();
            document.getElementById('special_instructions').value = '';
            if (orderType === 'dine_in') {
                document.getElementById('table_number').value = '';
            }

            // Redirect to order management after 1 second
            setTimeout(() => {
                window.location.href = '{{ route("restaurant.index") }}';
            }, 1000);
        } else {
            alert('❌ Gagal membuat order:\n' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan saat membuat order');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
</x-app-layout>
