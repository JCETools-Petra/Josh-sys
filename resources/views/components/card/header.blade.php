@props(['divider' => true])

@php
    $classes = 'flex items-center justify-between ';

    if ($divider) {
        $classes .= 'pb-4 mb-4 border-b border-gray-200 dark:border-gray-700';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
