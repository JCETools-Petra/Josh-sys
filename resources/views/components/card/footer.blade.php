@props(['divider' => true])

@php
    $classes = '';

    if ($divider) {
        $classes .= 'pt-4 mt-4 border-t border-gray-200 dark:border-gray-700';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
