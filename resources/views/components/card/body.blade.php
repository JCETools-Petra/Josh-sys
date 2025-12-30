@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'text-gray-700 dark:text-gray-300 ' . $class]) }}>
    {{ $slot }}
</div>
