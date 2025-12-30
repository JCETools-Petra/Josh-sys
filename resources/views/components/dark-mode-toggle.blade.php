{{-- Dark Mode Toggle Component --}}
<div x-data="{
    isDark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.isDark = !this.isDark;
        window.toggleTheme();
    }
}"
x-init="
    window.addEventListener('theme-changed', (e) => {
        isDark = e.detail.theme === 'dark';
    });
"
class="relative inline-flex items-center">
    <button
        @click="toggle"
        type="button"
        class="relative inline-flex items-center justify-center p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        title="Toggle Dark Mode"
    >
        {{-- Sun Icon (Light Mode) --}}
        <svg
            x-show="!isDark"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 rotate-90 scale-50"
            x-transition:enter-end="opacity-100 rotate-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 rotate-0 scale-100"
            x-transition:leave-end="opacity-0 -rotate-90 scale-50"
            class="w-5 h-5 text-yellow-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
            />
        </svg>

        {{-- Moon Icon (Dark Mode) --}}
        <svg
            x-show="isDark"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 rotate-90 scale-50"
            x-transition:enter-end="opacity-100 rotate-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 rotate-0 scale-100"
            x-transition:leave-end="opacity-0 -rotate-90 scale-50"
            class="w-5 h-5 text-blue-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
        </svg>
    </button>
</div>
