@props(['size' => 'md'])

@php
    $sizeClasses = [
        'sm' => 'text-base font-medium',
        'md' => 'text-lg font-semibold',
        'lg' => 'text-xl font-bold',
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<h3 {{ $attributes->merge(['class' => $sizeClass . ' text-gray-900 dark:text-white']) }}>
    {{ $slot }}
</h3>
