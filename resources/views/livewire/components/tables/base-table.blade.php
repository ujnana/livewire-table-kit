@php
    use Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\ActionColumn;

    $alignmentClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

    $responsiveClasses = [
        'sm' => 'hidden sm:table-cell',
        'md' => 'hidden md:table-cell',
        'lg' => 'hidden lg:table-cell',
    ];
@endphp

<div class="space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 lg:flex-1 xl:grid-cols-5">
            @foreach ($availableFilters as $filter)
                <flux:field wire:key="filter-{{ $filter->key }}">
                    @if($filter->label)
                        <flux:label>{{ $filter->label }}</flux:label>
                    @endif

                    @if ($filter->type === 'select')
                        <flux:select size="sm" placeholder="{{ $filter->placeholder ?? 'Select' }}"
                                     wire:model.live="filters.{{ $filter->key }}">
                            <flux:select.option
                                    value="">{{ $filter->placeholder !== '' ? 'Semua '.$filter->placeholder : 'All' }}</flux:select.option>
                            @foreach ($filter->options as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @elseif ($filter->type === 'radio')
                        <flux:radio.group wire:model.live="filters.{{ $filter->key }}" class="gap-2">
                            <flux:radio value="" label="{{ $filter->placeholder !== '' ? $filter->placeholder : 'All' }}" />
                            @foreach ($filter->options as $value => $label)
                                <flux:radio value="{{ $value }}" label="{{ $label }}" />
                            @endforeach
                        </flux:radio.group>
                    @elseif ($filter->type === 'checkbox')
                        <flux:checkbox.group wire:model.live="filters.{{ $filter->key }}" class="flex-col gap-2">
                            @foreach ($filter->options as $value => $label)
                                <flux:checkbox value="{{ $value }}" label="{{ $label }}" />
                            @endforeach
                        </flux:checkbox.group>
                    @else
                        <flux:input size="sm"
                                    :type="$filter->type"
                                    wire:model.live.debounce.300ms="filters.{{ $filter->key }}"
                                    :placeholder="$filter->placeholder"
                        />
                    @endif
                </flux:field>
            @endforeach
        </div>

        <div class="flex w-full flex-col gap-3 lg:w-auto lg:flex-row lg:items-center lg:justify-end">
            <div class="flex w-full items-center gap-2 lg:w-auto">
                @if ($this->hasSearch())
                    <flux:field class="flex-1 lg:w-80 lg:flex-none">
                        <flux:input size="sm" icon="magnifying-glass" wire:model.live.debounce.300ms="search"
                                    placeholder="Search table data..."/>
                    </flux:field>
                @endif

                @if ($this->supportsExport())
                    <flux:dropdown align="end">
                        <flux:button size="sm" variant="outline" icon="arrow-down-tray">
                            Export
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item as="button" type="button" wire:click="exportCsv">
                                CSV
                            </flux:menu.item>
                            <flux:menu.item as="button" type="button" wire:click="exportXlsx">
                                XLSX
                            </flux:menu.item>
                            <flux:menu.item as="button" type="button" wire:click="exportPdf">
                                PDF
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @endif
            </div>

            @if ($this->supportsBulkDelete() && $selected !== [])
                <flux:button size="sm" variant="danger" wire:click="bulkDelete" class="w-full lg:w-auto">
                    <span class="lg:hidden">Delete ({{ count($selected) }})</span>
                    <span class="hidden lg:inline">Delete Selected ({{ count($selected) }})</span>
                </flux:button>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900/60">
                <tr class="text-xs tracking-wide text-zinc-500 dark:text-zinc-400">
                    @if ($this->supportsBulkDelete())
                        <th class="sticky left-0 z-10 w-12 bg-zinc-50 px-4 py-3 text-left dark:bg-zinc-900">
                            <input
                                    type="checkbox"
                                    wire:model.live="selectAllPage"
                                    class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-900"
                            >
                        </th>
                    @endif

                    @foreach ($columns as $column)
                        @php
                            $headerAlignment = $alignmentClasses[$column->headerAlignment] ?? 'text-left';
                            $resolvedSortField = $column->sortField ?? $column->field;
                            $isActionColumn = $column instanceof ActionColumn;
                            $responsiveClass = $column->hiddenOn ? ($responsiveClasses[$column->hiddenOn] ?? '') : '';
                        @endphp

                        <th class="px-4 py-3 whitespace-nowrap {{ $headerAlignment }} {{ $isActionColumn ? 'sticky right-0 z-10 bg-zinc-50 dark:bg-zinc-900' : '' }} {{ $responsiveClass }}">
                            @if ($column->sortable && $resolvedSortField)
                                <button
                                        type="button"
                                        wire:click="sortBy('{{ $resolvedSortField }}')"
                                        class="inline-flex items-center gap-2 font-medium text-zinc-600 transition hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white"
                                >
                                    <span>{{ $column->label }}</span>

                                    @if ($sortField === $resolvedSortField)
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            @else
                                <span class="font-medium">{{ $column->label }}</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($rows as $row)
                    <tr wire:key="table-row-{{ $this->resolveRowKey($row) }}" class="bg-white dark:bg-zinc-900">
                        @if ($this->supportsBulkDelete())
                            <td class="sticky left-0 z-10 bg-white px-4 py-3 align-center dark:bg-zinc-900">
                                <input
                                        type="checkbox"
                                        value="{{ $this->resolveRowKey($row) }}"
                                        wire:model.live="selected"
                                        class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-900"
                                >
                            </td>
                        @endif

                        @foreach ($columns as $column)
                            @php
                                $alignment = $alignmentClasses[$column->alignment] ?? 'text-left';
                                $value = $this->resolveColumnValue($row, $column);
                                $isActionColumn = $column instanceof ActionColumn;
                                $responsiveClass = $column->hiddenOn ? ($responsiveClasses[$column->hiddenOn] ?? '') : '';
                            @endphp

                            <td class="px-4 py-3 align-center whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300 {{ $alignment }} {{ $isActionColumn ? 'sticky right-0 z-10 bg-white dark:bg-zinc-900' : '' }} {{ $responsiveClass }}">
                                @if ($isActionColumn)
                                    @include($column->view, ['row' => $row, 'column' => $column, 'value' => $value])
                                @elseif ($column->view)
                                    @include($column->view, ['row' => $row, 'column' => $column, 'value' => $value])
                                @elseif ($column->isHtml)
                                    {!! $value !!}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($this->supportsBulkDelete() ? 1 : 0) }}" class="p-0">
                            @include($emptyStateView, ['emptyState' => $emptyState])
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col gap-4 px-1 sm:grid sm:grid-cols-3 sm:items-center">
        <!-- Top Row (Mobile) / Left & Center (Desktop) -->
        <div class="flex items-center justify-between sm:contents">
            <!-- Per Page (Left) -->
            <div class="flex items-center gap-2">
                <flux:text class="text-xs text-zinc-500 sm:text-sm">Per page</flux:text>
                <flux:field class="w-[4rem] sm:w-[4.5rem]">
                    <flux:select size="xs" wire:model.live="perPage">
                        @foreach ($perPageOptions as $option)
                            <flux:select.option value="{{ $option }}">{{ $option === 'all' ? 'All' : $option }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <!-- Showing Info (Mobile: Right / Desktop: Center) -->
            <div class="flex justify-end sm:justify-center">
                <flux:text class="text-xs text-zinc-500 sm:text-sm">
                    Showing <b>{{ $rows->firstItem() ?? 0 }}</b> to <b>{{ $rows->lastItem() ?? 0 }}</b> of <b>{{ $rows->total() }}</b> entries
                </flux:text>
            </div>
        </div>

        <!-- Pagination (Mobile: Next Row / Desktop: Right) -->
        <div class="flex w-full justify-center sm:w-auto sm:justify-end">
            {{ $rows->links($paginationView) }}
        </div>
    </div>
</div>
