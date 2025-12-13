<?php

namespace App\DataTable\Type\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Type de filtre pour les plages de dates.
 */
class DateRangeFilterType implements FilterTypeInterface
{
    public function apply(QueryBuilder $queryBuilder, string $alias, string $field, mixed $value, array $options = []): void
    {
        if (empty($value)) {
            return;
        }

        if (is_array($value)) {
            $from = $value['from'] ?? null;
            $to = $value['to'] ?? null;

            if (!empty($from)) {
                $parameterNameFrom = 'filter_' . $field . '_from_' . uniqid();
                $queryBuilder
                    ->andWhere(sprintf('%s.%s >= :%s', $alias, $field, $parameterNameFrom))
                    ->setParameter($parameterNameFrom, new \DateTime($from));
            }

            if (!empty($to)) {
                $parameterNameTo = 'filter_' . $field . '_to_' . uniqid();
                $toDate = new \DateTime($to);
                $toDate->setTime(23, 59, 59);
                $queryBuilder
                    ->andWhere(sprintf('%s.%s <= :%s', $alias, $field, $parameterNameTo))
                    ->setParameter($parameterNameTo, $toDate);
            }
        } else {
            $parameterName = 'filter_' . $field . '_' . uniqid();
            $date = new \DateTime($value);
            $dateEnd = clone $date;
            $dateEnd->setTime(23, 59, 59);
            
            $queryBuilder
                ->andWhere(sprintf('%s.%s >= :%s_from', $alias, $field, $parameterName))
                ->andWhere(sprintf('%s.%s <= :%s_to', $alias, $field, $parameterName))
                ->setParameter($parameterName . '_from', $date)
                ->setParameter($parameterName . '_to', $dateEnd);
        }
    }

    public function supports(string $type): bool
    {
        return in_array($type, ['datetime', 'date', 'datetime_immutable', 'date_immutable'], true);
    }

    public function getName(): string
    {
        return 'date_range';
    }
}

