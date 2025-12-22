<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Kategori Finansial') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.financial-categories.update', $financialCategory->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Property Selection -->
                        <div class="mb-4">
                            <label for="property_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Properti <span class="text-red-600">*</span></label>
                            <select
                                name="property_id"
                                id="property_id"
                                required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm @error('property_id') border-red-500 @enderror"
                            >
                                <option value="">Pilih Properti</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id', $financialCategory->property_id) == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('property_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Code -->
                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Kategori <span class="text-red-600">*</span></label>
                            <input
                                type="text"
                                name="code"
                                id="code"
                                value="{{ old('code', $financialCategory->code) }}"
                                required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm @error('code') border-red-500 @enderror"
                                placeholder="Contoh: FO_SALARY"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kode unik untuk kategori ini (huruf besar, gunakan underscore)</p>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kategori <span class="text-red-600">*</span></label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $financialCategory->name) }}"
                                required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm @error('name') border-red-500 @enderror"
                                placeholder="Contoh: Gaji & Tunjangan Front Office"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Parent Category -->
                        <div class="mb-4">
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Kategori (Opsional)</label>
                            <select
                                name="parent_id"
                                id="parent_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm font-mono text-sm @error('parent_id') border-red-500 @enderror"
                            >
                                <option value="">Tidak Ada (Root Level)</option>
                                @php
                                    $currentRoot = null;
                                @endphp
                                @foreach($parentCategories as $parent)
                                    @if($parent['level'] === 0 && $currentRoot !== $parent['root'])
                                        @if($currentRoot !== null)
                                            </optgroup>
                                        @endif
                                        @php $currentRoot = $parent['root']; @endphp
                                        <optgroup label="{{ $parent['root'] }} ({{ ucfirst($parent['type']) }})">
                                    @endif
                                    <option value="{{ $parent['id'] }}" {{ old('parent_id', $financialCategory->parent_id) == $parent['id'] ? 'selected' : '' }}>
                                        {{ $parent['display'] }}
                                    </option>
                                @endforeach
                                @if($currentRoot !== null)
                                    </optgroup>
                                @endif
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kosongkan jika ini kategori departemen utama. Kategori diurutkan secara hierarkis.</p>
                            @error('parent_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe <span class="text-red-600">*</span></label>
                            <select
                                name="type"
                                id="type"
                                required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm @error('type') border-red-500 @enderror"
                            >
                                <option value="expense" {{ old('type', $financialCategory->type) == 'expense' ? 'selected' : '' }}>Expense (Pengeluaran)</option>
                                <option value="revenue" {{ old('type', $financialCategory->type) == 'revenue' ? 'selected' : '' }}>Revenue (Pendapatan)</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-4">
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan Tampilan</label>
                            <input
                                type="number"
                                name="sort_order"
                                id="sort_order"
                                value="{{ old('sort_order', $financialCategory->sort_order) }}"
                                min="0"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm @error('sort_order') border-red-500 @enderror"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Angka lebih kecil akan tampil lebih dulu</p>
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flags -->
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <input
                                    type="checkbox"
                                    name="is_payroll"
                                    id="is_payroll"
                                    value="1"
                                    {{ old('is_payroll', $financialCategory->is_payroll) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                >
                                <label for="is_payroll" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Kategori Payroll (Gaji & Tunjangan)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="allows_manual_input"
                                    id="allows_manual_input"
                                    value="1"
                                    {{ old('allows_manual_input', $financialCategory->allows_manual_input) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                >
                                <label for="allows_manual_input" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Izinkan Input Manual (centang untuk expense, kosongkan untuk revenue auto-calculated)
                                </label>
                            </div>
                        </div>

                        <!-- Info Box -->
                        @if($financialCategory->children()->count() > 0)
                            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <strong>Info:</strong> Kategori ini memiliki {{ $financialCategory->children()->count() }} sub-kategori.
                                </p>
                            </div>
                        @endif

                        <!-- Buttons -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="{{ route('admin.financial-categories.index', ['property_id' => $financialCategory->property_id]) }}" class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Update Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
