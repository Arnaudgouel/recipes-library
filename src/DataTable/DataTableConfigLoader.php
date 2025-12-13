<?php

namespace App\DataTable;

use App\DataTable\Configurator\DataTableConfiguratorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Service qui charge automatiquement les configurations de DataTable.
 */
class DataTableConfigLoader
{
    private array $loadedConfigs = [];

    /**
     * @param iterable<DataTableConfiguratorInterface> $configurators
     */
    public function __construct(
        #[AutowireIterator('datatable.configurator')]
        private readonly iterable $configurators
    ) {
    }

    public function loadForEntity(string $entityClass): void
    {
        if (isset($this->loadedConfigs[$entityClass])) {
            return;
        }

        foreach ($this->configurators as $configurator) {
            if ($configurator->supports($entityClass)) {
                $configurator->configure();
                $this->loadedConfigs[$entityClass] = true;
                return;
            }
        }
    }
}

