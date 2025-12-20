<tr class="{{ $category->is_payroll ? 'bg-yellow-50 dark:bg-yellow-900' : '' }}">
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
        {{ $category->code }}
    </td>
    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
        <div style="padding-left: {{ $level * 20 }}px;">
            @if($level > 0)
                <span class="text-gray-400">{{ str_repeat('└─ ', 1) }}</span>
            @endif
            <span class="font-medium">{{ $category->name }}</span>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $category->property->name ?? '-' }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->type === 'revenue' ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' }}">
            {{ $category->type === 'revenue' ? 'Revenue' : 'Expense' }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        @if($category->is_payroll)
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100">
                Payroll
            </span>
        @endif
        @if($category->allows_manual_input)
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">
                Manual Input
            </span>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <a href="{{ route('admin.financial-categories.edit', $category->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
            Edit
        </a>
        <form action="{{ route('admin.financial-categories.destroy', $category->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                Hapus
            </button>
        </form>
    </td>
</tr>

@foreach($category->children as $child)
    @include('admin.financial-categories.partials.category-row', ['category' => $child, 'level' => $level + 1])
@endforeach
