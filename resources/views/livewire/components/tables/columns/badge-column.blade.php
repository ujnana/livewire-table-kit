@php
    $badgeValue = (string) $value;

    $colorClasses = [
        'default' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
        'primary' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
        'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
        'danger' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
    ];

    $resolvedColor = $column->getColorMap()[$badgeValue] ?? $column->getColor() ?? 'default';
    $badgeClass = $colorClasses[$resolvedColor] ?? $colorClasses['default'];
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClass }}">
    {{ $badgeValue }}
</span>
