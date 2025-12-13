<?php

namespace App\DataTable\Type\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Type de filtre pour les choix (select/choice).
 */
class ChoiceFilterType implements FilterTypeInterface
{
    public function apply(QueryBuilder $queryBuilder, string $alias, string $field, mixed $value, array $options = []): void
    {
        if (empty($value)) {
            return;
        }

        $parameterName = 'filter_' . $field . '_' . uniqid();
        $queryBuilder
            ->andWhere(sprintf('%s.%s = :%s', $alias, $field, $parameterName))
            ->setParameter($parameterName, $value);
    }

    public function supports(string $type): bool
    {
        return $type === 'choice';
    }

    public function getName(): string
    {
        return 'choice';
    }
}

