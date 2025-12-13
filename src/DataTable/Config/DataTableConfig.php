<?php

namespace App\DataTable\Config;

/**
 * Configuration complète d'un DataTable.
 */
class DataTableConfig
{
    /** @var FieldConfig[] */
    private array $fields = [];

    /** @var FilterConfig[] */
    private array $filters = [];

    /** @var ActionConfig[] */
    private array $actions = [];

    public function __construct(
        public readonly string $name,
        public readonly string $entityClass,
        public readonly array $options = []
    ) {
    }

    public function addField(FieldConfig $field): void
    {
        $this->fields[$field->name] = $field;
    }

    public function addFilter(FilterConfig $filter): void
    {
        $this->filters[$filter->name] = $filter;
    }

    public function addAction(ActionConfig $action): void
    {
        $this->actions[$action->name] = $action;
    }

    /**
     * @return ActionConfig[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function getAction(string $name): ?ActionConfig
    {
        return $this->actions[$name] ?? null;
    }

    /**
     * @return FieldConfig[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $name): ?FieldConfig
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @return FilterConfig[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilter(string $name): ?FilterConfig
    {
        return $this->filters[$name] ?? null;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }
}

