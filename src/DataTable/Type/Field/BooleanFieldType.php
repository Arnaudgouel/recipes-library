<?php

namespace App\DataTable\Type\Field;

/**
 * Type de champ pour les booléens.
 */
class BooleanFieldType implements FieldTypeInterface
{
    public function render(mixed $value, array $options = []): string
    {
        if ($value === null) {
            return $options['null_value'] ?? '';
        }

        $trueLabel = $options['true_label'] ?? 'Oui';
        $falseLabel = $options['false_label'] ?? 'Non';

        return $value ? htmlspecialchars($trueLabel) : htmlspecialchars($falseLabel);
    }

    public function supports(string $type): bool
    {
        return $type === 'boolean';
    }

    public function getName(): string
    {
        return 'boolean';
    }
}

