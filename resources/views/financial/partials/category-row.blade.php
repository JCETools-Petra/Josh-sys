{{-- Recursive Category Row Component --}}
@php
    $indentClass = 'pl-' . ($category['level'] * 6 + 6);
    $isBold = $category['has_children'] || $category['is_payroll'];
    $bgClass = '';

    if ($category['level'] === 0) {
        $bgClass = 'bg-gray-100 dark:bg-gray-700';
    } elseif ($category['is_payroll']) {
        $bgClass = 'bg-yellow-50 dark:bg-yellow-900';
    } elseif ($category['has_children']) {
        $bgClass = 'bg-gray-50 dark:bg-gray-750';
    }
@endphp

<tr class="{{ $bgClass }}">
    <td class="px-6 py-2 text-sm {{ $isBold ? 'font-bold' : '' }} text-gray-900 dark:text-gray-100" style="padding-left: {{ ($category['level'] * 1.5 + 1.5) }}rem">
        {{ $category['name'] }}
        @if($category['code'])
            <span class="ml-2 text-xs text-blue-600 dark:text-blue-400">(Auto)</span>
        @endif
        @if($category['is_payroll'])
            <span class="ml-2 text-xs text-yellow-600 dark:text-yellow-400">(Payroll)</span>
        @endif
    </td>
    <td class="px-6 py-2 text-right text-sm {{ $isBold ? 'font-semibold' : '' }} text-gray-900 dark:text-gray-100">
        @if($category['actual_current'] != 0 || !$category['has_children'])
            Rp {{ number_format($category['actual_current'], 0, ',', '.') }}
        @endif
    </td>
    <td class="px-6 py-2 text-right text-sm {{ $isBold ? 'font-semibold' : '' }} text-gray-900 dark:text-gray-100">
        @if($category['budget_current'] != 0 || !$category['has_children'])
            Rp {{ number_format($category['budget_current'], 0, ',', '.') }}
        @endif
    </td>
    <td class="px-6 py-2 text-right text-sm {{ $isBold ? 'font-semibold' : '' }} border-r border-gray-300 {{ $category['variance_current'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
        @if($category['variance_current'] != 0 || !$category['has_children'])
            Rp {{ number_format($category['variance_current'], 0, ',', '.') }}
        @endif
    </td>
    <td class="px-6 py-2 text-right text-sm {{ $isBold ? 'font-semibold' : '' }} text-gray-900 dark:text-gray-100">
        @if($category['actual_ytd'] != 0 || !$category['has_children'])
            Rp {{ number_format($category['actual_ytd'], 0, ',', '.') }}
        @endif
    </td>
    <td class="px-6 py-2 text-right text-sm {{ $isBold ? 'font-semibold' : '' }} text-gray-900 dark:text-gray-100">
        @if($category['budget_ytd'] != 0 || !$category['has_children'])
            Rp {{ number_format($category['budget_ytd'], 0, ',', '.') }}
        @endif
    </td>
    <td class="px-6 py-2 text-right text-sm {{ $isBold ? 'font-semibold' : '' }} {{ $category['variance_ytd'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
        @if($category['variance_ytd'] != 0 || !$category['has_children'])
            Rp {{ number_format($category['variance_ytd'], 0, ',', '.') }}
        @endif
    </td>
</tr>

{{-- Recursively render children --}}
@if($category['has_children'] && count($category['children']) > 0)
    @foreach($category['children'] as $child)
        @include('financial.partials.category-row', ['category' => $child])
    @endforeach
@endif
