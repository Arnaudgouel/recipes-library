<?php

namespace App\DataTable\Type\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Type de filtre pour les chaînes de caractères (recherche LIKE).
 */
class StringFilterType implements FilterTypeInterface
{
    public function apply(QueryBuilder $queryBuilder, string $alias, string $field, mixed $value, array $options = []): void
    {
        if (empty($value)) {
            return;
        }

        $parameterName = 'filter_' . $field . '_' . uniqid();
        $queryBuilder
            ->andWhere(sprintf('LOWER(%s.%s) LIKE LOWER(:%s)', $alias, $field, $parameterName))
            ->setParameter($parameterName, '%' . $value . '%');
    }

    public function supports(string $type): bool
    {
        return in_array($type, ['string', 'text'], true);
    }

    public function getName(): string
    {
        return 'string';
    }
}

