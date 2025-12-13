<?php

namespace App\DataTable\Config;

/**
 * Configuration d'un champ dans le DataTable.
 */
class FieldConfig
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public array $options = []
    ) {
    }

    public function getLabel(): string
    {
        return $this->options['label'] ?? $this->name;
    }

    public function isSortable(): bool
    {
        return $this->options['sortable'] ?? false;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function isVisible(): bool
    {
        return $this->options['visible'] ?? true;
    }

    public function getTemplate(): ?string
    {
        return $this->options['template'] ?? null;
    }

    public function getRequiredRole(): ?string
    {
        return $this->options['role'] ?? null;
    }

    public function requiresRole(): bool
    {
        return $this->getRequiredRole() !== null;
    }
}

