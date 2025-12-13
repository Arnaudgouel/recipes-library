<?php

namespace App\DataTable\Type\Field;

/**
 * Interface pour les types de champs du DataTable.
 */
interface FieldTypeInterface
{
    /**
     * Rend la valeur d'un champ pour l'affichage.
     */
    public function render(mixed $value, array $options = []): string;

    /**
     * Vérifie si ce type supporte le type de champ donné.
     */
    public function supports(string $type): bool;

    /**
     * Retourne le nom du type.
     */
    public function getName(): string;
}

