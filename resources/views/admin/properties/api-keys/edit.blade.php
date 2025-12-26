<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit API Key - ') }}{{ $property->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.properties.api-keys.update', [$property, $apiKey]) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Nama API Key')" />
                            <x-text-input
                                id="name"
                                class="block mt-1 w-full"
                                type="text"
                                name="name"
                                :value="old('name', $apiKey->name)"
                                required
                                autofocus
                                placeholder="Contoh: Website Booking Utama" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Nama untuk mengidentifikasi API key ini.
                            </p>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="allowed_origins" :value="__('Allowed Origins (Opsional)')" />
                            <textarea
                                id="allowed_origins"
                                name="allowed_origins"
                                rows="3"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="https://booking.example.com, *.example.com"
                            >{{ old('allowed_origins', $apiKey->allowed_origins) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Domain yang diizinkan mengakses API. Pisahkan dengan koma. Kosongkan untuk mengizinkan semua domain.
                            </p>
                            <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                                <p class="text-sm text-blue-800 dark:text-blue-200 font-medium mb-1">Contoh format:</p>
                                <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1 list-disc list-inside">
                                    <li><code>https://booking.example.com</code> - Domain spesifik</li>
                                    <li><code>*.example.com</code> - Semua subdomain</li>
                                    <li><code>https://site1.com, https://site2.com</code> - Multiple domains</li>
                                </ul>
                            </div>
                            <x-input-error :messages="$errors->get('allowed_origins')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input
                                    id="is_active"
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', $apiKey->is_active) ? 'checked' : '' }}
                                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">API Key Aktif</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Nonaktifkan jika ingin melarang sementara penggunaan API key ini tanpa menghapusnya.
                            </p>
                        </div>

                        <!-- Info Box -->
                        <div class="mt-6 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Informasi API Key</h4>
                            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">API Key:</span>
                                    <code class="text-xs bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">{{ Str::limit($apiKey->key, 20, '...') }}</code>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Dibuat:</span>
                                    <span>{{ $apiKey->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Terakhir digunakan:</span>
                                    <span>
                                        @if($apiKey->last_used_at)
                                            {{ $apiKey->last_used_at->diffForHumans() }}
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 italic">Belum pernah</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Warning about API key -->
                        <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Catatan</h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>API key tidak dapat diubah. Jika Anda perlu API key baru, buat API key yang baru dan hapus yang lama.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <a href="{{ route('admin.properties.api-keys.index', $property) }}"
                               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
