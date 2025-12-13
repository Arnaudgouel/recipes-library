<?php

namespace App\DataTable\Type\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface pour les types de filtres du DataTable.
 */
interface FilterTypeInterface
{
    /**
     * Applique le filtre au QueryBuilder.
     */
    public function apply(QueryBuilder $queryBuilder, string $alias, string $field, mixed $value, array $options = []): void;

    /**
     * Vérifie si ce type supporte le type de champ donné.
     */
    public function supports(string $type): bool;

    /**
     * Retourne le nom du type.
     */
    public function getName(): string;
}

