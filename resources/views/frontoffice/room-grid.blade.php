<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        @if (session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Sukses!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif

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
                         onclick="showRoomDetail({{ $room->id }})">
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $room->room_number }}</div>
                            <div class="text-xs opacity-90">{{ $room->roomType->name ?? 'Standard' }}</div>
                        </div>

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

                        <div class="mt-3 text-center">
                            <span class="inline-block bg-white bg-opacity-30 text-xs px-2 py-1 rounded">
                                {{ $room->status_label }}
                            </span>
                        </div>

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

    <div id="roomDetailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
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

                <div class="mt-6 flex justify-between items-center">
                    <button onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
                        Tutup
                    </button>
                    <div class="flex space-x-3">
                        <button type="button" id="mark_clean_btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition hidden">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Tandai Sudah Dibersihkan
                        </button>
                        <button type="button" id="print_invoice_btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition hidden">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Invoice
                        </button>
                        <button type="button" id="checkout_btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition hidden">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Check-Out
                        </button>
                        <button type="button" id="checkin_btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition hidden">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Check-In
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="checkInModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <form action="{{ route('frontoffice.check-in') }}" method="POST" id="checkInForm">
                @csrf

                @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-6 mt-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-6 mt-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
                @endif

                <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center sticky top-0">
                    <h2 class="text-xl font-bold">Check-In Tamu Baru</h2>
                    <button type="button" onclick="closeCheckInModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <h3 class="text-lg font-semibold text-blue-900 mb-4">1. Kamar Dipilih</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kamar</label>
                                <input type="text" id="modal_selected_room_display" readonly
                                    class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-100 font-bold text-lg">
                                <input type="hidden" name="hotel_room_id" id="modal_hotel_room_id">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dengan Breakfast?</label>
                                <select name="with_breakfast" id="modal_with_breakfast" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="0">Tanpa Breakfast</option>
                                    <option value="1">Dengan Breakfast</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white p-3 rounded border border-blue-200">
                                <div class="text-sm text-gray-600 mb-1">BAR Aktif</div>
                                <div class="font-bold text-blue-800" id="modal_bar_display">{{ strtoupper(str_replace('_', ' ', $barActive)) }}</div>
                            </div>
                            <div class="bg-blue-50 p-3 rounded border border-blue-300">
                                <div class="text-sm text-gray-600 mb-1">Referensi Harga BAR Aktif</div>
                                <div class="font-bold text-blue-700 text-lg" id="modal_bar_reference_price">Rp 0</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Malam (Rp) *</label>
                            <input type="number" name="room_rate_per_night" id="modal_room_rate_per_night" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 font-semibold text-lg text-green-700"
                                placeholder="Masukkan harga per malam">
                            <p class="mt-1 text-xs text-gray-500">FO dapat menyesuaikan harga sesuai kebutuhan (referensi BAR di atas)</p>
                        </div>
                    </div>

                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <h3 class="text-lg font-semibold text-green-900 mb-4">2. Data Tamu</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Depan *</label>
                                <input type="text" name="guest[first_name]" required
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Belakang</label>
                                <input type="text" name="guest[last_name]"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="guest[email]"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon *</label>
                                <input type="text" name="guest[phone]" required placeholder="08123456789"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Identitas *</label>
                                <select name="guest[id_type]" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="ktp">KTP</option>
                                    <option value="passport">Passport</option>
                                    <option value="sim">SIM</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Identitas *</label>
                                <input type="text" name="guest[id_number]" required
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                                <textarea name="guest[address]" rows="2"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                                <input type="text" name="guest[city]"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                        <h3 class="text-lg font-semibold text-purple-900 mb-4">3. Detail Menginap</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Check-In *</label>
                                <input type="datetime-local" name="check_in_date" id="modal_check_in_date" required
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                    value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Check-Out *</label>
                                <input type="datetime-local" name="check_out_date" id="modal_check_out_date" required
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                    value="{{ now()->addDay()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Malam</label>
                                <input type="text" id="modal_nights_display" readonly
                                    class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-100" value="1 malam">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dewasa *</label>
                                <input type="number" name="adults" required min="1" value="1"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Anak-anak</label>
                                <input type="number" name="children" min="0" value="0"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-4">4. Sumber Booking</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sumber *</label>
                                <select name="source" id="modal_source" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                                    <option value="walk_in">Walk-In</option>
                                    <option value="ota">OTA (Online Travel Agent)</option>
                                    <option value="ta">Travel Agent</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="government">Government</option>
                                    <option value="compliment">Compliment</option>
                                    <option value="house_use">House Use</option>
                                    <option value="affiliate">Affiliate</option>
                                    <option value="online">Online Direct</option>
                                </select>
                            </div>
                            <div id="modal_ota_fields" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama OTA</label>
                                <input type="text" name="ota_name"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                                    placeholder="Contoh: Traveloka, Booking.com">
                            </div>
                            <div id="modal_booking_id_field" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Booking ID / Kode</label>
                                <input type="text" name="ota_booking_id"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Permintaan Khusus</label>
                                <textarea name="special_requests" rows="2"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                                    placeholder="Extra bed, high floor, allergies, dll"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Deposit Section -->
                    <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
                        <h3 class="text-lg font-semibold text-indigo-900 mb-4">5. Deposit (Opsional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Deposit Diminta (Rp)</label>
                                <input type="number" name="deposit_amount" id="checkin_deposit_amount" min="0" step="10000"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="0" onchange="calculateCheckinDepositBalance()">
                                <p class="mt-1 text-xs text-gray-500">Deposit yang diminta dari tamu</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deposit Sudah Dibayar (Rp)</label>
                                <input type="number" name="deposit_paid" id="checkin_deposit_paid" min="0" step="10000"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="0" onchange="calculateCheckinDepositBalance()">
                                <p class="mt-1 text-xs text-gray-500">Jumlah deposit yang diterima saat check-in</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran Deposit</label>
                                <select name="deposit_payment_method" id="checkin_deposit_payment_method" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="cash">Cash</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div id="checkin_deposit_balance_info" class="md:col-span-3 hidden">
                                <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold">Sisa Deposit yang Belum Dibayar:</span>
                                        <span class="text-lg font-bold" id="checkin_deposit_balance_display">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-300 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Biaya</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Harga per malam:</span>
                                <span class="font-semibold" id="modal_rate_display">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jumlah malam:</span>
                                <span class="font-semibold" id="modal_nights_summary">1</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold" id="modal_subtotal_display">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Pajak (10%):</span>
                                <span id="modal_tax_display">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Service Charge (5%):</span>
                                <span id="modal_service_display">Rp 0</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between text-lg font-bold text-blue-600">
                                <span>Total:</span>
                                <span id="modal_total_display">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end space-x-3 sticky bottom-0">
                    <button type="button" onclick="closeCheckInModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" id="submit_checkin_btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span id="submit_btn_text">Check-In Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Store all rooms data in JavaScript object
        const roomsData = @json($rooms->keyBy('id'));

        function showRoomDetail(roomId) {
            const room = roomsData[roomId];
            if (!room) {
                console.error('Room not found:', roomId);
                return;
            }
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

            // Button visibility based on room status
            const checkinBtn = document.getElementById('checkin_btn');
            const checkoutBtn = document.getElementById('checkout_btn');
            const printInvoiceBtn = document.getElementById('print_invoice_btn');
            const markCleanBtn = document.getElementById('mark_clean_btn');

            // Hide all buttons first
            checkinBtn.classList.add('hidden');
            checkoutBtn.classList.add('hidden');
            printInvoiceBtn.classList.add('hidden');
            markCleanBtn.classList.add('hidden');

            if (room.status === 'vacant_clean') {
                // Show check-in button for available rooms
                checkinBtn.classList.remove('hidden');
                checkinBtn.onclick = function() {
                    closeModal();
                    showCheckInModal(room.id);
                };
            } else if (room.status === 'vacant_dirty') {
                // Show mark clean button for dirty rooms
                markCleanBtn.classList.remove('hidden');
                markCleanBtn.onclick = function() {
                    if (confirm('Konfirmasi bahwa kamar ' + room.room_number + ' sudah dibersihkan?')) {
                        window.location.href = `/frontoffice/mark-clean/${room.id}`;
                    }
                };
            } else if (room.status === 'occupied' && room.current_stay) {
                // Show checkout and print invoice buttons for occupied rooms
                checkoutBtn.classList.remove('hidden');
                printInvoiceBtn.classList.remove('hidden');

                checkoutBtn.onclick = function() {
                    if (confirm('Apakah Anda yakin ingin check-out tamu ini?')) {
                        window.location.href = `/frontoffice/checkout/${room.current_stay.id}`;
                    }
                };

                printInvoiceBtn.onclick = function() {
                    window.open(`/frontoffice/invoice/${room.current_stay.id}`, '_blank');
                };
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

        // Global variables for check-in modal
        let currentRoomData = null;
        const activeBar = '{{ $barActive }}';
        const allRoomTypes = @json($roomTypes);

        // Check-In Modal Functions
        function showCheckInModal(roomId) {
            try {
                const room = roomsData[roomId];
                if (!room) {
                    alert('Data kamar tidak ditemukan!');
                    return;
                }

                currentRoomData = room;

                // Set room information
                document.getElementById('modal_selected_room_display').value = 'Kamar ' + room.room_number + ' - ' + (room.room_type?.name || 'Standard');
                document.getElementById('modal_hotel_room_id').value = room.id;

                // Calculate price
                updateRoomPrice();

                // Show modal
                document.getElementById('checkInModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Error opening check-in modal:', error);
                alert('Terjadi kesalahan saat membuka form check-in: ' + error.message);
            }
        }

        function updateRoomPrice() {
            if (!currentRoomData || !currentRoomData.room_type || !currentRoomData.room_type.pricing_rule) {
                document.getElementById('modal_bar_reference_price').textContent = 'Rp 0';
                document.getElementById('modal_room_rate_per_night').value = 0;
                if (typeof window.updateModalSummary === 'function') {
                    window.updateModalSummary();
                }
                return;
            }

            const withBreakfast = document.getElementById('modal_with_breakfast').value === '1';
            const baseRoomTypeName = currentRoomData.room_type.name;
            let pricingRule = currentRoomData.room_type.pricing_rule;

            if (withBreakfast) {
                const breakfastRoomType = allRoomTypes.find(rt =>
                    rt.name.includes(baseRoomTypeName) &&
                    rt.name.toLowerCase().includes('breakfast') &&
                    rt.id !== currentRoomData.room_type.id
                );

                if (breakfastRoomType && breakfastRoomType.pricing_rule) {
                    pricingRule = breakfastRoomType.pricing_rule;
                }
            }

            let referencePrice = 0;
            switch(activeBar) {
                case 'bar_1': referencePrice = parseFloat(pricingRule.bar_1 || 0); break;
                case 'bar_2': referencePrice = parseFloat(pricingRule.bar_2 || 0); break;
                case 'bar_3': referencePrice = parseFloat(pricingRule.bar_3 || 0); break;
                case 'bar_4': referencePrice = parseFloat(pricingRule.bar_4 || 0); break;
                case 'bar_5': referencePrice = parseFloat(pricingRule.bar_5 || 0); break;
                default: referencePrice = parseFloat(pricingRule.bar_1 || 0);
            }

            document.getElementById('modal_bar_reference_price').textContent = 'Rp ' + Math.round(referencePrice).toLocaleString('id-ID');
            document.getElementById('modal_room_rate_per_night').value = Math.round(referencePrice);

            if (typeof window.updateModalSummary === 'function') {
                window.updateModalSummary();
            }
        }

        function closeCheckInModal() {
            document.getElementById('checkInModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('modal_room_rate_per_night').value = '';
            currentRoomData = null;
        }

        // Close modal when clicking outside
        document.getElementById('checkInModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCheckInModal();
            }
        });

        // Check-In Form Logic
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM loaded, initializing form listeners...');

            const checkInForm = document.querySelector('#checkInModal form');
            const checkInModal = document.getElementById('checkInModal');
            const rateInput = document.getElementById('modal_room_rate_per_night');
            const checkInInput = document.getElementById('modal_check_in_date');
            const checkOutInput = document.getElementById('modal_check_out_date');
            const sourceSelect = document.getElementById('modal_source');
            const otaFields = document.getElementById('modal_ota_fields');
            const bookingIdField = document.getElementById('modal_booking_id_field');
            const breakfastSelect = document.getElementById('modal_with_breakfast');
            const submitBtn = document.getElementById('submit_checkin_btn');
            const submitBtnText = document.getElementById('submit_btn_text');

            if (!checkInForm) {
                console.error('❌ Check-in form not found!');
                return;
            }

            console.log('✅ Form found:', checkInForm);
            console.log('Form action:', checkInForm.action);
            console.log('Form method:', checkInForm.method);

            // Auto-open modal if there are validation errors
            @if ($errors->any() || session('error'))
            console.log('⚠️ Validation errors detected, opening modal...');
            if (checkInModal) {
                checkInModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            @endif

            checkInForm.addEventListener('submit', function(e) {
                console.log('🚀 Form submit triggered!');

                // Check if all required fields are filled
                const requiredFields = checkInForm.querySelectorAll('[required]');
                let missingFields = [];

                console.log('Total required fields:', requiredFields.length);

                requiredFields.forEach(field => {
                    const value = field.value ? field.value.trim() : '';
                    console.log(`Field ${field.name || field.id}: "${value}"`);

                    if (!value) {
                        const label = field.closest('div').querySelector('label')?.textContent || field.name || field.id;
                        missingFields.push({
                            name: field.name || field.id,
                            label: label,
                            element: field
                        });
                    }
                });

                if (missingFields.length > 0) {
                    e.preventDefault();
                    console.error('❌ Missing fields:', missingFields.map(f => f.name));

                    missingFields.forEach(field => {
                        field.element.classList.add('border-red-500', 'border-2');
                    });

                    let errorMessage = '⚠️ FORM TIDAK LENGKAP!\n\nField yang harus diisi:\n\n';
                    missingFields.forEach((field, index) => {
                        errorMessage += `${index + 1}. ${field.label}\n`;
                    });

                    alert(errorMessage);
                    if (missingFields[0] && missingFields[0].element) {
                        missingFields[0].element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        missingFields[0].element.focus();
                    }
                    return false;
                }

                console.log('✅ All fields valid! Submitting form...');
                checkInForm.querySelectorAll('.border-red-500').forEach(el => {
                    el.classList.remove('border-red-500', 'border-2');
                });

                // Show loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    if (submitBtnText) {
                        submitBtnText.textContent = 'Memproses...';
                    }
                }
            });

            // Remove error highlight when field is filled
            checkInForm.querySelectorAll('[required]').forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value && this.value.trim() !== '') {
                        this.classList.remove('border-red-500', 'border-2');
                    }
                });
            });

            // PERBAIKAN: Kurung kurawal '}' yang salah dihapus dari sini

            // Update price when breakfast selection changes
            if (breakfastSelect) {
                breakfastSelect.addEventListener('change', function() {
                    updateRoomPrice();
                });
            }

            // Show/hide OTA fields
            if (sourceSelect) {
                sourceSelect.addEventListener('change', function() {
                    if (this.value === 'ota') {
                        otaFields.style.display = 'block';
                        bookingIdField.style.display = 'block';
                    } else {
                        otaFields.style.display = 'none';
                        bookingIdField.style.display = 'none';
                    }
                });
            }

            // Calculate nights and update summary
            function calculateModalNights() {
                const checkIn = new Date(checkInInput.value);
                const checkOut = new Date(checkOutInput.value);
                if (checkIn && checkOut && checkOut > checkIn) {
                    const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                    document.getElementById('modal_nights_display').value = nights + ' malam';
                    document.getElementById('modal_nights_summary').textContent = nights;
                    return nights;
                }
                return 1;
            }

            function formatRupiah(amount) {
                return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function updateModalSummary() {
                const rate = parseFloat(rateInput.value) || 0;
                const nights = calculateModalNights();
                const subtotal = rate * nights;
                const tax = subtotal * 0.10;
                const service = subtotal * 0.05;
                const total = subtotal + tax + service;

                document.getElementById('modal_rate_display').textContent = formatRupiah(rate);
                document.getElementById('modal_subtotal_display').textContent = formatRupiah(subtotal);
                document.getElementById('modal_tax_display').textContent = formatRupiah(tax);
                document.getElementById('modal_service_display').textContent = formatRupiah(service);
                document.getElementById('modal_total_display').textContent = formatRupiah(total);
            }

            function calculateCheckinDepositBalance() {
                const depositAmount = parseFloat(document.getElementById('checkin_deposit_amount').value) || 0;
                const depositPaid = parseFloat(document.getElementById('checkin_deposit_paid').value) || 0;
                const balance = Math.max(0, depositAmount - depositPaid);

                const balanceInfo = document.getElementById('checkin_deposit_balance_info');
                const balanceDisplay = document.getElementById('checkin_deposit_balance_display');

                if (depositAmount > 0) {
                    balanceInfo.classList.remove('hidden');
                    balanceDisplay.textContent = formatRupiah(balance);

                    // Change color based on status
                    const balanceDiv = balanceInfo.querySelector('div');
                    if (balance === 0 && depositPaid > 0) {
                        // Fully paid
                        balanceDiv.className = 'bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded';
                    } else if (depositPaid > 0 && balance > 0) {
                        // Partially paid
                        balanceDiv.className = 'bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded';
                    } else {
                        // Not paid yet
                        balanceDiv.className = 'bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded';
                    }
                } else {
                    balanceInfo.classList.add('hidden');
                }
            }

            // Expose function to global scope so it can be called from outside
            window.updateModalSummary = updateModalSummary;
            window.calculateCheckinDepositBalance = calculateCheckinDepositBalance;

            // Update summary when any field changes
            if (rateInput) rateInput.addEventListener('input', updateModalSummary);
            if (checkInInput) checkInInput.addEventListener('change', updateModalSummary);
            if (checkOutInput) checkOutInput.addEventListener('change', updateModalSummary);

            // Initial calculation
            updateModalSummary();
        });
    </script>
</x-app-layout>