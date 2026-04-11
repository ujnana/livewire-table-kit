<?php

it('declares the package metadata needed for publishing', function (): void {
    $composer = json_decode((string) file_get_contents(__DIR__.'/../composer.json'), true);

    expect($composer)->toBeArray();
    expect($composer['name'])->toBe('unlab/livewire-table-kit');
    expect($composer['autoload']['psr-4']['Unlab\\LivewireTableKit\\'])->toBe('src/');
    expect($composer['extra']['laravel']['providers'])->toContain('Unlab\\LivewireTableKit\\LivewireTableKitServiceProvider');
});
