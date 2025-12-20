<tr>
    <td class="indent-{{ $category['level'] }}">
        {{ $category['name'] }}{{ $category['code'] ? ' (Auto)' : '' }}
    </td>
    <td class="text-right">{{ number_format($category['actual_current'], 0) }}</td>
    <td class="text-right">{{ number_format($category['budget_current'], 0) }}</td>
    <td class="text-right {{ $category['type'] === 'revenue' ? ($category['variance_current'] >= 0 ? 'positive' : 'negative') : ($category['variance_current'] <= 0 ? 'positive' : 'negative') }}">
        {{ number_format($category['variance_current'], 0) }}
    </td>
    <td class="text-right">{{ number_format($category['actual_ytd'], 0) }}</td>
    <td class="text-right">{{ number_format($category['budget_ytd'], 0) }}</td>
    <td class="text-right {{ $category['type'] === 'revenue' ? ($category['variance_ytd'] >= 0 ? 'positive' : 'negative') : ($category['variance_ytd'] <= 0 ? 'positive' : 'negative') }}">
        {{ number_format($category['variance_ytd'], 0) }}
    </td>
</tr>

@foreach($category['children'] ?? [] as $child)
    @include('financial.pdf.partials.category-row-pdf', ['category' => $child])
@endforeach
