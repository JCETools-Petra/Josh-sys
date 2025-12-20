<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Restaurant Management</h1>
            <p class="text-gray-600">{{ $property->name ?? 'Property Name' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('frontoffice.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
            <a href="{{ route('restaurant.pos') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Buat Order Baru
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Order Hari Ini</div>
            <div class="text-2xl font-bold text-gray-800">0</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-blue-600">Pending</div>
            <div class="text-2xl font-bold text-blue-700">0</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-yellow-600">Preparing</div>
            <div class="text-2xl font-bold text-yellow-700">0</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-green-600">Pendapatan Hari Ini</div>
            <div class="text-xl font-bold text-green-700">Rp 0</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="#orders" onclick="switchTab('orders')" id="tab_orders" class="tab-link border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                    Active Orders
                </a>
                <a href="#menu" onclick="switchTab('menu')" id="tab_menu" class="tab-link border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Menu Management
                </a>
                <a href="#history" onclick="switchTab('history')" id="tab_history" class="tab-link border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Order History
                </a>
            </nav>
        </div>
    </div>

    <!-- Tab Content: Active Orders -->
    <div id="content_orders" class="tab-content">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Order Aktif</h2>

            <div class="text-center py-12 text-gray-500">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-lg mb-4">Belum ada order aktif</p>
                <a href="{{ route('restaurant.pos') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition">
                    Buat Order Pertama
                </a>
            </div>
        </div>
    </div>

    <!-- Tab Content: Menu Management -->
    <div id="content_menu" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Manajemen Menu</h2>
                <button onclick="showAddMenuItem()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Menu
                </button>
            </div>

            <div class="text-center py-12 text-gray-500">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <p class="text-lg mb-4">Belum ada menu item</p>
                <p class="text-sm text-gray-400 mb-4">Mulai tambahkan menu makanan dan minuman untuk restaurant Anda</p>
                <button onclick="showAddMenuItem()" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                    Tambah Menu Pertama
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content: Order History -->
    <div id="content_history" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Riwayat Order</h2>

            <!-- Date Filter -->
            <div class="mb-4 flex space-x-3">
                <input type="date" class="border-gray-300 rounded-lg shadow-sm" value="{{ date('Y-m-d') }}">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    Filter
                </button>
            </div>

            <div class="text-center py-12 text-gray-500">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg">Belum ada riwayat order</p>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active state from all tabs
    document.querySelectorAll('.tab-link').forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    document.getElementById('content_' + tabName).classList.remove('hidden');

    // Add active state to selected tab
    const activeTab = document.getElementById('tab_' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

function showAddMenuItem() {
    alert('Fitur tambah menu item akan segera tersedia!\n\nUntuk saat ini, menu item dapat ditambahkan melalui database atau tinker.');
}
</script>
</x-app-layout>
