<?php

namespace App\DataTable\Config;

/**
 * Configuration d'une action dans le DataTable.
 */
class ActionConfig
{
    public function __construct(
        public readonly string $name,
        public readonly array $options = []
    ) {
    }

    public function getLabel(): string
    {
        return $this->options['label'] ?? $this->name;
    }

    public function getRoute(): ?string
    {
        return $this->options['route'] ?? null;
    }

    public function getUrl(): ?string
    {
        return $this->options['url'] ?? null;
    }

    public function getTemplate(): ?string
    {
        return $this->options['template'] ?? null;
    }

    public function getRouteParams(): array
    {
        return $this->options['route_params'] ?? [];
    }

    public function getCssClass(): string
    {
        return $this->options['css_class'] ?? 'btn btn-sm btn-primary';
    }

    public function getAttributes(): array
    {
        return $this->options['attributes'] ?? [];
    }

    public function isVisible(mixed $entity): bool
    {
        if (isset($this->options['visible_callback']) && is_callable($this->options['visible_callback'])) {
            return call_user_func($this->options['visible_callback'], $entity);
        }

        return $this->options['visible'] ?? true;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }
}

