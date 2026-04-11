<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Livewire\Components\Tables\Columns;

class BadgeColumn extends Column
{
    protected ?string $color = null;

    protected ?string $variant = null;

    protected ?string $size = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $colorMap = null;

    public function __construct(
        public string $label,
        public mixed $value = null,
        public ?string $view = null,
    ) {
        parent::__construct($label, $value, 'livewire-table-kit::livewire.components.tables.columns.badge-column');
    }

    public static function make(string $label): static
    {
        return new static($label);
    }

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param  array<string, string>  $colorMap
     */
    public function colorMap(array $colorMap): static
    {
        $this->colorMap = $colorMap;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @return array<string, string>|null
     */
    public function getColorMap(): ?array
    {
        return $this->colorMap;
    }

    public function getVariant(): ?string
    {
        return $this->variant;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }
}
