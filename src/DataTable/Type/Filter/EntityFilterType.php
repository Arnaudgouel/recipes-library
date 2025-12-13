<?php

namespace App\DataTable\Type\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Type de filtre pour les entités associées.
 */
class EntityFilterType implements FilterTypeInterface
{
    public function apply(QueryBuilder $queryBuilder, string $alias, string $field, mixed $value, array $options = []): void
    {
        if (empty($value)) {
            return;
        }

        // Si c'est un ID, filtrer directement par l'identifier
        if (is_numeric($value)) {
            $joinAlias = $field;
            $identifier = $options['target_identifier'] ?? 'id';
            
            $joins = $queryBuilder->getDQLPart('join');
            $joinExists = false;
            if (isset($joins[$alias])) {
                foreach ($joins[$alias] as $join) {
                    if ($join->getAlias() === $joinAlias) {
                        $joinExists = true;
                        break;
                    }
                }
            }

            if (!$joinExists) {
                $queryBuilder->leftJoin($alias . '.' . $field, $joinAlias);
            }

            $parameterName = 'filter_' . $field . '_' . uniqid();
            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%s', $joinAlias, $identifier, $parameterName))
                ->setParameter($parameterName, $value);
            return;
        }

        // Sinon, recherche textuelle
        $joinAlias = $field;
        $targetField = $options['target_field'] ?? 'nom';

        $joins = $queryBuilder->getDQLPart('join');
        $joinExists = false;
        if (isset($joins[$alias])) {
            foreach ($joins[$alias] as $join) {
                if ($join->getAlias() === $joinAlias) {
                    $joinExists = true;
                    break;
                }
            }
        }

        if (!$joinExists) {
            $queryBuilder->leftJoin($alias . '.' . $field, $joinAlias);
        }

        $parameterName = 'filter_' . $field . '_' . uniqid();
        $queryBuilder
            ->andWhere(sprintf('LOWER(%s.%s) LIKE LOWER(:%s)', $joinAlias, $targetField, $parameterName))
            ->setParameter($parameterName, '%' . $value . '%');
    }

    public function supports(string $type): bool
    {
        return in_array($type, ['entity', 'collection'], true);
    }

    public function getName(): string
    {
        return 'entity';
    }
}

