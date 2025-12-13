<?php

namespace App\DataTable\Type\Field;

/**
 * Type de champ pour les choix (select/choice).
 */
class ChoiceFieldType implements FieldTypeInterface
{
    public function render(mixed $value, array $options = []): string
    {
        if ($value === null) {
            return $options['empty_value'] ?? '';
        }

        $choices = $options['choices'] ?? [];
        
        if (!is_array($choices)) {
            return htmlspecialchars((string) $value);
        }

        $valueString = (string) $value;

        if (isset($choices[$valueString])) {
            return htmlspecialchars((string) $choices[$valueString]);
        }

        return htmlspecialchars((string) ($options['unknown_value'] ?? $valueString));
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

