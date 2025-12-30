@props(['class' => ''])

<tbody {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ' . $class]) }}>
    {{ $slot }}
</tbody>
