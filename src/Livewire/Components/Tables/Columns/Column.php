<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Livewire\Components\Tables\Columns;

use Closure;
use InvalidArgumentException;

class Column
{
    public bool $sortable = false;

    public ?string $sortField = null;

    public bool $searchable = false;

    public ?string $searchField = null;

    public bool $isRawExpression = false;

    public bool $isHtml = false;

    public bool $exportable = true;

    public string $alignment = 'left';

    public string $headerAlignment = 'left';

    public ?string $field = null;

    public function __construct(
        public string $label,
        public mixed $value = null,
        public ?string $view = null,
    ) {}

    public static function make(string $label): static
    {
        return new static($label);
    }

    public function field(string $field): static
    {
        $this->field = $field;

        if ($this->value === null) {
            $this->value = $field;
        }

        if ($this->sortable && $this->sortField === null) {
            $this->sortField = $field;
        }

        if ($this->searchable && $this->searchField === null) {
            $this->searchField = $field;
        }

        return $this;
    }

    public function value(Closure $callback): static
    {
        $this->value = $callback;

        return $this;
    }

    public function view(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function sortable(?string $field = null): static
    {
        $this->sortable = true;
        $this->sortField = $field ?? $this->sortField ?? $this->field;

        return $this;
    }

    public function searchable(?string $field = null): static
    {
        $resolvedField = $field ?? $this->searchField ?? $this->field;

        if ($resolvedField === null) {
            throw new InvalidArgumentException('Searchable columns require a field name.');
        }

        $this->searchable = true;
        $this->searchField = $resolvedField;
        $this->isRawExpression = false;

        return $this;
    }

    public function searchableRaw(string $expression): static
    {
        $this->searchable = true;
        $this->searchField = $expression;
        $this->isRawExpression = true;

        return $this;
    }

    public function align(string $alignment): static
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function headerAlign(string $headerAlignment): static
    {
        $this->headerAlignment = $headerAlignment;

        return $this;
    }

    public function html(): static
    {
        $this->isHtml = true;

        return $this;
    }

    public function exportable(bool $exportable = true): static
    {
        $this->exportable = $exportable;

        return $this;
    }
}
