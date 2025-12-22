<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('API Key Berhasil Dibuat - ') }}{{ $property->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Alert -->
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">API Key Berhasil Dibuat!</h3>
                        <p class="mt-2 text-sm text-green-700 dark:text-green-300">
                            API key Anda telah berhasil dibuat. Simpan API key ini dengan aman karena tidak akan ditampilkan lagi.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Detail API Key</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nama API Key
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">{{ $apiKey->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                API Key
                            </label>
                            <div class="flex items-center space-x-2">
                                <code id="apiKeyValue" class="flex-1 block p-3 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-mono break-all">
                                    {{ $apiKey->key }}
                                </code>
                                <button
                                    onclick="copyToClipboard()"
                                    class="inline-flex items-center px-4 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="ml-1">Copy</span>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                API key ini hanya ditampilkan sekali. Pastikan Anda menyalinnya sekarang.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Allowed Origins
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                @if($apiKey->allowed_origins)
                                    {{ $apiKey->allowed_origins }}
                                @else
                                    <span class="text-gray-500 dark:text-gray-400 italic">Semua domain diizinkan</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status
                            </label>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Aktif
                            </span>
                        </div>
                    </div>

                    <!-- Usage Example -->
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Contoh Penggunaan</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">JavaScript (Fetch API):</p>
                        <pre class="text-xs bg-gray-800 text-gray-100 p-3 rounded overflow-x-auto"><code>fetch('{{ url('/api/properties/' . $property->id . '/room-pricing') }}', {
  headers: {
    'X-API-Key': '{{ $apiKey->key }}'
  }
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>

                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 mt-4">cURL:</p>
                        <pre class="text-xs bg-gray-800 text-gray-100 p-3 rounded overflow-x-auto"><code>curl -H "X-API-Key: {{ $apiKey->key }}" \
  {{ url('/api/properties/' . $property->id . '/room-pricing') }}</code></pre>
                    </div>

                    <!-- Warning Box -->
                    <div class="mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Peringatan Keamanan</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Jangan bagikan API key ini kepada siapapun</li>
                                        <li>Jangan commit API key ke version control (Git)</li>
                                        <li>Simpan di environment variables atau file konfigurasi yang aman</li>
                                        <li>Jika API key terkompromi, segera nonaktifkan atau hapus</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 space-x-4">
                        <a href="{{ route('admin.properties.api-keys.index', $property) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Kembali ke Daftar API Keys') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const apiKey = document.getElementById('apiKeyValue').textContent.trim();
            navigator.clipboard.writeText(apiKey).then(() => {
                // Show success feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="ml-1">Copied!</span>';
                button.classList.remove('bg-blue-600', 'hover:bg-blue-500');
                button.classList.add('bg-green-600', 'hover:bg-green-500');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('bg-green-600', 'hover:bg-green-500');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-500');
                }, 2000);
            }).catch(err => {
                alert('Failed to copy API key. Please copy manually.');
            });
        }
    </script>
</x-app-layout>
