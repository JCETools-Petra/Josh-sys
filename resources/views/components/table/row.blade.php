@props(['hoverable' => true, 'clickable' => false])

@php
    $classes = 'transition-colors ';

    if ($hoverable) {
        $classes .= 'hover:bg-gray-50 dark:hover:bg-gray-700 ';
    }

    if ($clickable) {
        $classes .= 'cursor-pointer ';
    }
@endphp

<tr {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</tr>
