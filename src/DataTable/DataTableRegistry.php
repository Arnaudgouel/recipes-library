<?php

namespace App\DataTable;

use App\DataTable\Config\DataTableConfig;

/**
 * Registre des configurations de DataTable.
 */
class DataTableRegistry
{
    /** @var DataTableConfig[] */
    private array $configs = [];

    public function register(DataTableConfig $config): void
    {
        $this->configs[$config->name] = $config;
    }

    public function get(string $name): ?DataTableConfig
    {
        return $this->configs[$name] ?? null;
    }

    public function getByEntityClass(string $entityClass): ?DataTableConfig
    {
        foreach ($this->configs as $config) {
            if ($config->entityClass === $entityClass) {
                return $config;
            }
        }

        return null;
    }

    /**
     * @return DataTableConfig[]
     */
    public function all(): array
    {
        return $this->configs;
    }
}

