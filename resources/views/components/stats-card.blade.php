@props([
    'title',
    'value',
    'icon' => null,
    'iconColor' => 'blue',
    'trend' => null,
    'trendDirection' => null,
    'description' => null
])

@php
    $iconColorClasses = [
        'blue' => 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300',
        'green' => 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300',
        'red' => 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300',
        'purple' => 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300',
        'gray' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
    ];

    $iconColorClass = $iconColorClasses[$iconColor] ?? $iconColorClasses['blue'];

    $trendClasses = [
        'up' => 'text-green-600 dark:text-green-400',
        'down' => 'text-red-600 dark:text-red-400',
        'neutral' => 'text-gray-600 dark:text-gray-400',
    ];

    $trendClass = $trendClasses[$trendDirection] ?? $trendClasses['neutral'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                {{ $title }}
            </p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $value }}
            </p>

            @if($trend || $description)
                <div class="mt-2 flex items-center space-x-2 text-sm">
                    @if($trend)
                        <span class="{{ $trendClass }} flex items-center">
                            @if($trendDirection === 'up')
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            @elseif($trendDirection === 'down')
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            @endif
                            {{ $trend }}
                        </span>
                    @endif

                    @if($description)
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ $description }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if($icon)
            <div class="flex-shrink-0 ml-4">
                <div class="w-12 h-12 rounded-lg {{ $iconColorClass }} flex items-center justify-center">
                    {!! $icon !!}
                </div>
            </div>
        @endif
    </div>
</div>
