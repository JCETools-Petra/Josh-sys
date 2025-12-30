@props(['align' => 'left', 'nowrap' => false])

@php
    $alignmentClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

    $alignClass = $alignmentClasses[$align] ?? $alignmentClasses['left'];
    $wrapClass = $nowrap ? 'whitespace-nowrap' : '';
@endphp

<td {{ $attributes->merge(['class' => 'px-6 py-4 text-sm text-gray-900 dark:text-gray-100 ' . $alignClass . ' ' . $wrapClass]) }}>
    {{ $slot }}
</td>
