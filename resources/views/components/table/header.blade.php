@props(['sortable' => false, 'align' => 'left'])

@php
    $alignmentClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

    $alignClass = $alignmentClasses[$align] ?? $alignmentClasses['left'];
@endphp

<th {{ $attributes->merge(['class' => 'px-6 py-3 ' . $alignClass . ' text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider']) }}>
    @if($sortable)
        <button class="inline-flex items-center space-x-1 group">
            <span>{{ $slot }}</span>
            <svg class="w-4 h-4 opacity-50 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
        </button>
    @else
        {{ $slot }}
    @endif
</th>
