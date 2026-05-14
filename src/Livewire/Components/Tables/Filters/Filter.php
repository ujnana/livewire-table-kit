<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Livewire\Components\Tables\Filters;

use Closure;

class Filter
{
    public function __construct(
        public string $key,
        public ?string $label = null,
        public string $type = 'select', // select, radio, checkbox, text, date, number
        public string $display = 'inline', // inline, dropdown
        public array $options = [],
        public mixed $default = null,
        public string $placeholder = '',
        public ?Closure $queryCallback = null,
    ) {}

    public static function make(string $key, string|array|null $label = null): static
    {
        return static::fromConfiguration($key, $label);
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function display(string $display): static
    {
        $this->display = $display;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;

        return $this;
    }

    public static function select(string $key, string|array|null $label = null, ?array $options = null): static
    {
        $filter = static::fromConfiguration($key, $label);

        return $filter->type('select')->options($options ?? $filter->options);
    }

    public static function text(string $key, string|array|null $label = null, string $placeholder = ''): static
    {
        $filter = static::fromConfiguration($key, $label);

        return $filter->type('text')->placeholder($filter->placeholder !== '' ? $filter->placeholder : $placeholder);
    }

    public static function date(string $key, string|array|null $label = null): static
    {
        return static::fromConfiguration($key, $label)->type('date');
    }

    public static function number(string $key, string|array|null $label = null, string $placeholder = ''): static
    {
        $filter = static::fromConfiguration($key, $label);

        return $filter->type('number')->placeholder($filter->placeholder !== '' ? $filter->placeholder : $placeholder);
    }

    public static function radio(string $key, string|array|null $label = null, ?array $options = null): static
    {
        $filter = static::fromConfiguration($key, $label);

        return $filter->type('radio')->options($options ?? $filter->options);
    }

    public static function checkbox(string $key, string|array|null $label = null, ?array $options = null): static
    {
        $filter = static::fromConfiguration($key, $label);

        return $filter->type('checkbox')->display('dropdown')->options($options ?? $filter->options);
    }

    protected static function fromConfiguration(string $key, string|array|null $label = null): static
    {
        if (is_array($label)) {
            $filter = new static(
                key: $key,
                label: $label['label'] ?? null,
                type: $label['type'] ?? 'select',
                display: $label['display'] ?? (($label['type'] ?? 'select') === 'checkbox' ? 'dropdown' : 'inline'),
                options: $label['options'] ?? [],
                default: $label['default'] ?? null,
                placeholder: $label['placeholder'] ?? '',
                queryCallback: $label['query'] ?? null,
            );

            return $filter;
        }

        return new static($key, $label);
    }
}
