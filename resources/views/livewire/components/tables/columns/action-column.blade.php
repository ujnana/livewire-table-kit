<flux:dropdown align="end">
    <flux:button size="sm" square icon="ellipsis-horizontal"></flux:button>

    <flux:menu>
        @foreach ($column->getActions() as $action)
            @php
                $resolvedUrl = $action->resolveUrl($row);
                $resolvedMethod = $action->resolveMethod($row);
                $isDanger = $action->color === 'danger';
            @endphp

            @if ($action->type === 'wire' && $resolvedMethod)
                <flux:menu.item
                        icon="{{ $action->icon }}"
                        wire:click="{{ $resolvedMethod }}"
                        variant="{{ $isDanger ? 'danger' : 'default' }}"
                        class="{{ $isDanger ? 'text-red-600 hover:bg-red-50' : '' }}"
                >
                    <div>
                        {{ $action->label }}
                    </div>
                </flux:menu.item>
            @elseif ($resolvedUrl)
                <flux:menu.item
                        icon="{{ $action->icon }}"
                        href="{{ $resolvedUrl }}"
                        variant="{{ $isDanger ? 'danger' : 'default' }}"
                        class="{{ $isDanger ? 'text-red-600 hover:bg-red-50' : '' }}"
                >
                    <div>
                        {{ $action->label }}
                    </div>
                </flux:menu.item>
            @endif
        @endforeach
    </flux:menu>
</flux:dropdown>
