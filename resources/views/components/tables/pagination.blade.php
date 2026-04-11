@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-2">
        <!-- Previous Button -->
        @if ($paginator->onFirstPage())
            <flux:button disabled>Previous</flux:button>
        @else
            <flux:button wire:click="previousPage" wire:loading.attr="disabled" rel="prev">
                Previous
            </flux:button>
        @endif

        <!-- Page Numbers -->
        <flux:button.group>
            @if($paginator->lastPage() <= 7)
                @for($page = 1; $page <= $paginator->lastPage(); $page++)
                    <flux:button
                        wire:click="gotoPage({{ $page }})"
                        :variant="$page == $paginator->currentPage() ? 'primary' : 'filled'"
                    >
                        {{ $page }}
                    </flux:button>
                @endfor
            @else
                <!-- First Page -->
                <flux:button
                    wire:click="gotoPage(1)"
                    :variant="1 == $paginator->currentPage() ? 'primary' : 'filled'"
                >
                    1
                </flux:button>

                @if($paginator->currentPage() > 3)
                    <flux:button variant="filled" disabled>
                        ...
                    </flux:button>
                @endif

                @php
                    $start = max(2, $paginator->currentPage() - 1);
                    $end = min($paginator->lastPage() - 1, $paginator->currentPage() + 1);
                @endphp

                @for($page = $start; $page <= $end; $page++)
                    <flux:button
                        wire:click="gotoPage({{ $page }})"
                        :variant="$page == $paginator->currentPage() ? 'primary' : 'filled'"
                    >
                        {{ $page }}
                    </flux:button>
                @endfor

                @if($paginator->currentPage() < $paginator->lastPage() - 2)
                    <flux:button variant="filled" disabled>
                        ...
                    </flux:button>
                @endif

                <!-- Last Page -->
                @if($paginator->lastPage() > 1)
                    <flux:button
                        wire:click="gotoPage({{ $paginator->lastPage() }})"
                        :variant="$paginator->lastPage() == $paginator->currentPage() ? 'primary' : 'filled'"
                    >
                        {{ $paginator->lastPage() }}
                    </flux:button>
                @endif
            @endif
        </flux:button.group>

        <!-- Next Button -->
        @if ($paginator->onLastPage())
            <flux:button disabled>Next</flux:button>
        @else
            <flux:button wire:click="nextPage" wire:loading.attr="disabled" rel="next">
                Next
            </flux:button>
        @endif
    </nav>
@endif
