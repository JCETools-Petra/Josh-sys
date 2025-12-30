@props(['title', 'subtitle' => null, 'badge' => null, 'dateRange' => null, 'subtitleBelow' => false])

<div class="flex items-center justify-between space-x-4 flex-wrap gap-3">
    {{-- Title Section --}}
    <div class="min-w-0 flex-1">
        <div class="flex items-center space-x-3">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                {{ $title }}
            </h1>

            @if($badge && !$subtitleBelow)
                <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 whitespace-nowrap">
                    {{ $badge }}
                </span>
            @endif

            @if($subtitle && !$subtitleBelow)
                <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400 hidden sm:inline">
                    {{ $subtitle }}
                </span>
            @endif
        </div>

        @if($subtitleBelow && $subtitle)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    {{-- Date Range Badge or Custom Slot --}}
    @if($dateRange)
        <div class="flex items-center space-x-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                {{ $dateRange }}
            </span>
        </div>
    @elseif(isset($actions))
        <div class="flex items-center space-x-2">
            {{ $actions }}
        </div>
    @endif
</div>
