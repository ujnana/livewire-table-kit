<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Livewire\Components\Tables\Columns\Actions;

use Closure;

class TableAction
{
    public function __construct(
        public string $label,
        public string $type = 'link',
        public mixed $url = null,
        public string|Closure|null $method = null,
        public string $color = 'ghost',
        public ?string $icon = null,
    ) {}

    public static function link(string $label, mixed $url, string $color = 'ghost', ?string $icon = null): static
    {
        return new static($label, 'link', $url, null, $color, $icon);
    }

    public static function wire(string $label, string|Closure $method, string $color = 'ghost', ?string $icon = null): static
    {
        return new static($label, 'wire', null, $method, $color, $icon);
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function resolveUrl(mixed $row): mixed
    {
        if ($this->url instanceof Closure) {
            return ($this->url)($row);
        }

        return $this->url;
    }

    public function resolveMethod(mixed $row): ?string
    {
        if ($this->method instanceof Closure) {
            return ($this->method)($row);
        }

        return $this->method;
    }
}
