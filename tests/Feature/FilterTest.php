<?php

declare(strict_types=1);

use Unlab\LivewireTableKit\Livewire\Components\Tables\Filters\Filter;

it('creates a radio filter helper with the expected type and options', function (): void {
    $filter = Filter::radio('status', 'Status', [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ])->default('active')->placeholder('All statuses');

    expect($filter->key)->toBe('status');
    expect($filter->label)->toBe('Status');
    expect($filter->type)->toBe('radio');
    expect($filter->options)->toBe([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]);
    expect($filter->default)->toBe('active');
    expect($filter->placeholder)->toBe('All statuses');
});
