<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Buat Housekeeping Task Baru</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ $property->name }}</p>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded">
        <ul class="list-disc list-inside text-red-700 dark:text-red-400">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="{{ route('housekeeping.tasks.store') }}" method="POST">
            @csrf

            <!-- Hotel Room -->
            <div class="mb-4">
                <label for="hotel_room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Kamar <span class="text-red-500">*</span>
                </label>
                <select name="hotel_room_id" id="hotel_room_id" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="">-- Pilih Kamar --</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ old('hotel_room_id') == $room->id ? 'selected' : '' }}>
                        {{ $room->room_number }} - {{ $room->roomType->name ?? 'Standard' }} ({{ $room->status_label }})
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Task Type -->
            <div class="mb-4">
                <label for="task_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tipe Task <span class="text-red-500">*</span>
                </label>
                <select name="task_type" id="task_type" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="">-- Pilih Tipe Task --</option>
                    <option value="daily_cleaning" {{ old('task_type') === 'daily_cleaning' ? 'selected' : '' }}>Daily Cleaning</option>
                    <option value="deep_cleaning" {{ old('task_type') === 'deep_cleaning' ? 'selected' : '' }}>Deep Cleaning</option>
                    <option value="turndown" {{ old('task_type') === 'turndown' ? 'selected' : '' }}>Turndown Service</option>
                    <option value="inspection" {{ old('task_type') === 'inspection' ? 'selected' : '' }}>Inspection</option>
                </select>
            </div>

            <!-- Task Date -->
            <div class="mb-4">
                <label for="task_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tanggal Task <span class="text-red-500">*</span>
                </label>
                <input type="date" name="task_date" id="task_date" required
                    value="{{ old('task_date', today()->toDateString()) }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Assigned To -->
            <div class="mb-4">
                <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Assign ke Staff HK
                </label>
                <select name="assigned_to" id="assigned_to"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="">-- Pilih Staff (Opsional) --</option>
                    @foreach($housekeepers as $hk)
                    <option value="{{ $hk->id }}" {{ old('assigned_to') == $hk->id ? 'selected' : '' }}>{{ $hk->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Priority -->
            <div class="mb-4">
                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Priority <span class="text-red-500">*</span>
                </label>
                <select name="priority" id="priority" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Catatan
                </label>
                <textarea name="notes" id="notes" rows="4"
                    placeholder="Catatan tambahan untuk task ini..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">{{ old('notes') }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    Buat Task
                </button>
                <a href="{{ route('housekeeping.tasks.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Task Type Information -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
        <h3 class="font-semibold text-blue-800 dark:text-blue-400 mb-2">Informasi Tipe Task:</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
            <li><strong>Daily Cleaning:</strong> Pembersihan harian standar (ganti linen, bersihkan kamar mandi, vacuum, dll)</li>
            <li><strong>Deep Cleaning:</strong> Pembersihan mendalam (cuci gorden, bersihkan AC, polish furniture, dll)</li>
            <li><strong>Turndown Service:</strong> Layanan turndown malam (lipat sprei, taruh coklat, nyalakan lampu malam)</li>
            <li><strong>Inspection:</strong> Inspeksi kualitas kebersihan kamar</li>
        </ul>
    </div>
</div>
</x-app-layout>
