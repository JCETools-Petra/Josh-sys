@props(['padding' => true, 'shadow' => true])

@php
    $classes = 'bg-white dark:bg-gray-800 rounded-lg overflow-hidden ';

    if ($padding) {
        $classes .= 'p-6 ';
    }

    if ($shadow) {
        $classes .= 'shadow-sm border border-gray-200 dark:border-gray-700';
    } else {
        $classes .= 'border border-gray-200 dark:border-gray-700';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
