<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
            <a href="{{ route('housekeeping.lost-found.index') }}" class="hover:text-blue-600">Lost & Found</a>
            <span>/</span>
            <a href="{{ route('housekeeping.lost-found.show', $lostAndFound) }}" class="hover:text-blue-600">{{ $lostAndFound->item_number }}</a>
            <span>/</span>
            <span>Proses Klaim</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Proses Klaim Barang</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ $lostAndFound->property->name }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Claim Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form action="{{ route('housekeeping.lost-found.claim', $lostAndFound) }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Pilih tamu yang terdaftar atau masukkan informasi pengklaim manual
                        </p>

                        <div class="space-y-4">
                            <!-- Guest Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Pilih Tamu (Opsional)
                                </label>
                                <select name="claimed_by_guest" id="guestSelect"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">-- Input Manual di Bawah --</option>
                                    @foreach($guests as $guest)
                                        <option value="{{ $guest->id }}"
                                            data-name="{{ $guest->full_name }}"
                                            data-phone="{{ $guest->phone }}"
                                            {{ old('claimed_by_guest') == $guest->id ? 'selected' : '' }}>
                                            {{ $guest->full_name }} - {{ $guest->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('claimed_by_guest')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    Atau masukkan informasi pengklaim manual:
                                </p>

                                <!-- Manual Name -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Pengklaim <span class="text-red-500" id="nameRequired">*</span>
                                    </label>
                                    <input type="text" name="claimed_by_name" id="manualName"
                                        value="{{ old('claimed_by_name') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Nama lengkap pengklaim">
                                    @error('claimed_by_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Manual Phone -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        No. Telepon <span class="text-red-500" id="phoneRequired">*</span>
                                    </label>
                                    <input type="text" name="claimed_by_phone" id="manualPhone"
                                        value="{{ old('claimed_by_phone') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Nomor telepon pengklaim">
                                    @error('claimed_by_phone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Claim Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Catatan Klaim
                                </label>
                                <textarea name="claim_notes" rows="3"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Catatan tambahan untuk klaim ini...">{{ old('claim_notes') }}</textarea>
                                @error('claim_notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                            Proses Klaim
                        </button>
                        <a href="{{ route('housekeeping.lost-found.show', $lostAndFound) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Item Info Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Info Barang</h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nomor</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ $lostAndFound->item_number }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nama Barang</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ $lostAndFound->item_name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Kategori</p>
                        <p class="text-gray-900 dark:text-white">{{ ucfirst($lostAndFound->category) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Deskripsi</p>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->description }}</p>
                    </div>

                    @if($lostAndFound->color)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Warna</p>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->color }}</p>
                    </div>
                    @endif

                    @if($lostAndFound->brand)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Brand</p>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->brand }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Lokasi Ditemukan</p>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->location_found }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tanggal Ditemukan</p>
                        <p class="text-gray-900 dark:text-white">{{ $lostAndFound->date_found->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            @if($lostAndFound->guest)
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow p-6 mt-6">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-400 mb-4">Tamu Terkait</h3>

                <div class="space-y-2">
                    <div>
                        <p class="text-sm text-blue-600 dark:text-blue-400">Nama</p>
                        <p class="text-blue-900 dark:text-blue-300 font-semibold">{{ $lostAndFound->guest->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-600 dark:text-blue-400">Telepon</p>
                        <p class="text-blue-900 dark:text-blue-300">{{ $lostAndFound->guest->phone }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const guestSelect = document.getElementById('guestSelect');
    const manualName = document.getElementById('manualName');
    const manualPhone = document.getElementById('manualPhone');
    const nameRequired = document.getElementById('nameRequired');
    const phoneRequired = document.getElementById('phoneRequired');

    guestSelect.addEventListener('change', function() {
        if (this.value) {
            // Guest selected - auto-fill and disable manual fields
            const selectedOption = this.options[this.selectedIndex];
            manualName.value = selectedOption.dataset.name || '';
            manualPhone.value = selectedOption.dataset.phone || '';
            manualName.disabled = true;
            manualPhone.disabled = true;
            nameRequired.style.display = 'none';
            phoneRequired.style.display = 'none';
        } else {
            // No guest selected - enable manual fields
            manualName.value = '';
            manualPhone.value = '';
            manualName.disabled = false;
            manualPhone.disabled = false;
            nameRequired.style.display = 'inline';
            phoneRequired.style.display = 'inline';
        }
    });

    // Trigger change event on page load to set initial state
    guestSelect.dispatchEvent(new Event('change'));
});
</script>
</x-app-layout>
