<?php

namespace App\DataTable\Type\Filter;

/**
 * Registre des types de filtres disponibles.
 */
class FilterTypeRegistry
{
    /** @var FilterTypeInterface[] */
    private array $types = [];

    public function __construct()
    {
        $this->register(new StringFilterType());
        $this->register(new EntityFilterType());
        $this->register(new DateRangeFilterType());
        $this->register(new ChoiceFilterType());
    }

    public function register(FilterTypeInterface $type): void
    {
        $this->types[$type->getName()] = $type;
    }

    public function get(string $name): ?FilterTypeInterface
    {
        return $this->types[$name] ?? null;
    }

    public function findForType(string $type): ?FilterTypeInterface
    {
        foreach ($this->types as $filterType) {
            if ($filterType->supports($type)) {
                return $filterType;
            }
        }

        return $this->types['string'] ?? null;
    }

    /**
     * @return FilterTypeInterface[]
     */
    public function all(): array
    {
        return $this->types;
    }
}

