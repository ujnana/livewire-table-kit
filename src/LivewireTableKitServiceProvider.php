<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit;

use Illuminate\Support\ServiceProvider;

class LivewireTableKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-table-kit');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/livewire-table-kit'),
        ], 'livewire-table-kit-views');
    }
}
