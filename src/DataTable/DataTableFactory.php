<?php

namespace App\DataTable;

use App\DataTable\Config\DataTableConfig;
use App\DataTable\DataProvider\DataProviderInterface;
use App\DataTable\DataProvider\DoctrineORMDataProvider;
use App\DataTable\Type\Field\FieldTypeRegistry;
use App\DataTable\Type\Filter\FilterTypeRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Factory pour créer et configurer les composants du DataTable.
 */
class DataTableFactory
{
    private FieldTypeRegistry $fieldTypeRegistry;
    private FilterTypeRegistry $filterTypeRegistry;
    private DataTableRegistry $dataTableRegistry;
    private DataProviderInterface $dataProvider;
    private FieldRenderer $fieldRenderer;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        EntityManagerInterface $entityManager,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->fieldTypeRegistry = new FieldTypeRegistry();
        $this->filterTypeRegistry = new FilterTypeRegistry();
        $this->dataTableRegistry = new DataTableRegistry();

        $this->dataProvider = new DoctrineORMDataProvider(
            $entityManager,
            $this->filterTypeRegistry
        );

        $this->fieldRenderer = new FieldRenderer(
            $this->fieldTypeRegistry,
            $propertyAccessor
        );
    }

    public function getFieldTypeRegistry(): FieldTypeRegistry
    {
        return $this->fieldTypeRegistry;
    }

    public function getFilterTypeRegistry(): FilterTypeRegistry
    {
        return $this->filterTypeRegistry;
    }

    public function getDataTableRegistry(): DataTableRegistry
    {
        return $this->dataTableRegistry;
    }

    public function getDataProvider(): DataProviderInterface
    {
        return $this->dataProvider;
    }

    public function getFieldRenderer(): FieldRenderer
    {
        return $this->fieldRenderer;
    }

    public function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor;
    }

    public function getOrCreateConfig(string $entityClass): DataTableConfig
    {
        $config = $this->dataTableRegistry->getByEntityClass($entityClass);
        if ($config !== null) {
            return $config;
        }

        $shortName = (new \ReflectionClass($entityClass))->getShortName();
        $name = 'app_' . strtolower($shortName);
        
        return new DataTableConfig($name, $entityClass);
    }
}

