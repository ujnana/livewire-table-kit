<div class="px-6 py-12 text-center">
    @if ($emptyState['icon'])
        <div class="mb-3 flex justify-center">
            <flux:icon :name="$emptyState['icon']" class="size-8 text-zinc-400 dark:text-zinc-500" />
        </div>
    @endif

    <div class="space-y-2">
        <h3 class="text-base font-semibold text-zinc-950 dark:text-white">{{ $emptyState['title'] }}</h3>
        <p class="mx-auto max-w-md text-sm text-zinc-500 dark:text-zinc-400">{{ $emptyState['message'] }}</p>
    </div>

    @if ($emptyState['buttonLabel'])
        <div class="mt-5 flex justify-center">
            @if ($emptyState['buttonUrl'])
                <flux:button :href="$emptyState['buttonUrl']" :icon="$emptyState['buttonIcon']" variant="primary">
                    {{ $emptyState['buttonLabel'] }}
                </flux:button>
            @elseif ($emptyState['buttonActionWire'])
                <flux:button wire:click="{{ $emptyState['buttonActionWire'] }}" :icon="$emptyState['buttonIcon']" variant="primary">
                    {{ $emptyState['buttonLabel'] }}
                </flux:button>
            @endif
        </div>
    @endif
</div>
