<?php

namespace App\DataTable\DataProvider;

use App\DataTable\Config\DataTableConfig;
use App\DataTable\Type\Filter\FilterTypeRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Fournisseur de données utilisant Doctrine ORM.
 */
class DoctrineORMDataProvider implements DataProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FilterTypeRegistry $filterTypeRegistry
    ) {
    }

    public function getData(DataTableConfig $config, array $parameters): array
    {
        $qb = $this->createQueryBuilder($config);
        $alias = 'e';
        $identifier = $this->getIdentifier($config->entityClass);
        $defaultSort = $parameters['sort_column'] ?? $identifier;

        $this->applyFilters($qb, $config, $alias, $parameters['filters'] ?? []);
        $this->applySort($qb, $config, $alias, $defaultSort, $parameters['sort_direction'] ?? 'ASC');

        $page = $parameters['page'] ?? 1;
        $itemsPerPage = $parameters['items_per_page'] ?? 10;
        $offset = ($page - 1) * $itemsPerPage;
        $qb->setFirstResult($offset)
           ->setMaxResults($itemsPerPage);

        return $qb->getQuery()->getResult();
    }

    public function getCount(DataTableConfig $config, array $parameters): int
    {
        $qb = $this->createQueryBuilder($config);
        $alias = 'e';
        $identifier = $this->getIdentifier($config->entityClass);

        $qb->select('COUNT(' . $alias . '.' . $identifier . ')');
        $qb->setFirstResult(null);
        $qb->setMaxResults(null);

        $this->applyFilters($qb, $config, $alias, $parameters['filters'] ?? []);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllData(DataTableConfig $config, array $parameters): array
    {
        $qb = $this->createQueryBuilder($config);
        $alias = 'e';
        $identifier = $this->getIdentifier($config->entityClass);
        $defaultSort = $parameters['sort_column'] ?? $identifier;

        $this->applyFilters($qb, $config, $alias, $parameters['filters'] ?? []);
        $this->applySort($qb, $config, $alias, $defaultSort, $parameters['sort_direction'] ?? 'ASC');

        $qb->setFirstResult(null)
           ->setMaxResults(null);

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère le nom de l'identifier (clé primaire) d'une entité.
     */
    private function getIdentifier(string $entityClass): string
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $identifiers = $metadata->getIdentifierFieldNames();
        
        return $identifiers[0] ?? 'id';
    }

    private function createQueryBuilder(DataTableConfig $config): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($config->entityClass, 'e');
    }

    private function applyFilters(QueryBuilder $qb, DataTableConfig $config, string $alias, array $filters): void
    {
        $metadata = $this->entityManager->getClassMetadata($config->entityClass);

        foreach ($filters as $fieldName => $value) {
            if (is_array($value)) {
                $hasValue = false;
                foreach ($value as $v) {
                    if (!empty($v)) {
                        $hasValue = true;
                        break;
                    }
                }
                if (!$hasValue) {
                    continue;
                }
            } elseif (empty($value)) {
                continue;
            }

            $filterConfig = $config->getFilter($fieldName);
            if ($filterConfig === null) {
                continue;
            }

            $filterType = $this->filterTypeRegistry->get($filterConfig->type);
            if ($filterType === null) {
                continue;
            }

            if ($metadata->hasAssociation($fieldName)) {
                $this->ensureJoin($qb, $alias, $fieldName);
            }

            $filterType->apply($qb, $alias, $fieldName, $value, $filterConfig->options ?? []);
        }
    }

    private function applySort(QueryBuilder $qb, DataTableConfig $config, string $alias, string $sortColumn, string $sortDirection): void
    {
        $metadata = $this->entityManager->getClassMetadata($config->entityClass);

        if ($metadata->hasAssociation($sortColumn)) {
            $fieldConfig = $config->getField($sortColumn);
            $associationMapping = $metadata->getAssociationMapping($sortColumn);
            $targetEntity = $associationMapping['targetEntity'];
            $targetIdentifier = $this->getIdentifier($targetEntity);
            
            $sortField = $targetIdentifier;
            if ($fieldConfig !== null && $fieldConfig->type === 'entity') {
                $sortField = $fieldConfig->getOption('target_field', $targetIdentifier);
            }
            
            $this->ensureJoin($qb, $alias, $sortColumn);
            $qb->orderBy($sortColumn . '.' . $sortField, $sortDirection);
        } else {
            $qb->orderBy($alias . '.' . $sortColumn, $sortDirection);
        }
    }

    private function ensureJoin(QueryBuilder $qb, string $alias, string $field): void
    {
        $joins = $qb->getDQLPart('join');
        $joinExists = false;
        
        if (isset($joins[$alias])) {
            foreach ($joins[$alias] as $join) {
                if ($join->getAlias() === $field) {
                    $joinExists = true;
                    break;
                }
            }
        }

        if (!$joinExists) {
            $qb->leftJoin($alias . '.' . $field, $field);
        }
    }
}

