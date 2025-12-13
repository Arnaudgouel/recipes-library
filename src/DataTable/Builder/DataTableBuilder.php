<?php

namespace App\DataTable\Builder;

use App\DataTable\Config\ActionConfig;
use App\DataTable\Config\DataTableConfig;
use App\DataTable\Config\FieldConfig;
use App\DataTable\Config\FilterConfig;
use App\DataTable\Exception\MissingAutocompleteRouteException;

/**
 * Builder pour construire la configuration d'un DataTable.
 */
class DataTableBuilder
{
    private DataTableConfig $config;

    public function __construct(string $name, string $entityClass, array $options = [])
    {
        $this->config = new DataTableConfig($name, $entityClass, $options);
    }

    public function addField(string $name, string $type, array $options = []): self
    {
        $this->config->addField(new FieldConfig($name, $type, $options));
        return $this;
    }

    public function addFilter(string $name, string $type, array $options = []): self
    {
        if ($type === 'entity' && ($options['autocomplete'] ?? false)) {
            if (!isset($options['route']) && !isset($options['alias'])) {
                $targetEntity = $options['target_entity'] ?? 'unknown';
                throw new MissingAutocompleteRouteException($name, $targetEntity);
            }
        }
        
        $this->config->addFilter(new FilterConfig($name, $type, $options));
        return $this;
    }

    public function addAction(string $name, array $options = []): self
    {
        $this->config->addAction(new ActionConfig($name, $options));
        return $this;
    }

    public function getConfig(): DataTableConfig
    {
        return $this->config;
    }
}

