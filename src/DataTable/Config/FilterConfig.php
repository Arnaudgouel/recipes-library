<?php

namespace App\DataTable\Config;

/**
 * Configuration d'un filtre dans le DataTable.
 */
class FilterConfig
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly array $options = []
    ) {
    }

    public function getLabel(): string
    {
        return $this->options['label'] ?? $this->name;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }
}

