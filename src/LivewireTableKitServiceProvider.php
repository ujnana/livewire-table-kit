<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit;

use Illuminate\Support\ServiceProvider;

class LivewireTableKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Unlab\LivewireTableKit\Console\Commands\MakeTableCommand::class,
                \Unlab\LivewireTableKit\Console\Commands\McpServerCommand::class,
                \Unlab\LivewireTableKit\Console\Commands\InstallMcpCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/livewire-table-kit.php', 'livewire-table-kit'
        );

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-table-kit');

        if ($this->app->runningInConsole()) {
            // Config publish
            $this->publishes([
                __DIR__.'/../config/livewire-table-kit.php' => config_path('livewire-table-kit.php'),
            ], 'livewire-table-kit-config');

            // Views publish
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/livewire-table-kit'),
            ], 'livewire-table-kit-views');

            // MCP AI Skills publish
            $this->publishes([
                __DIR__.'/../stubs/mcp.json.stub' => base_path('.mcp/livewire-table-kit.json'),
            ], 'livewire-table-kit-mcp');

            // Stubs publish
            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/vendor/livewire-table-kit'),
            ], 'livewire-table-kit-stubs');

            // Translations publish
            $this->publishes([
                __DIR__.'/../lang' => lang_path('vendor/livewire-table-kit'),
            ], 'livewire-table-kit-lang');
        }
    }
}
