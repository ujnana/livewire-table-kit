<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Livewire\Components\Tables\Columns;

class ActionColumn extends Column
{
    protected array $actions = [];

    public function __construct()
    {
        parent::__construct('', null, 'livewire-table-kit::livewire.components.tables.columns.action-column');

        $this->align('right');
        $this->headerAlign('right');
        $this->exportable(false);
    }

    public static function make(string $label = ''): static
    {
        return new static;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}
