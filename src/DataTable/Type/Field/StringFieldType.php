<?php

namespace App\DataTable\Type\Field;

/**
 * Type de champ pour les chaînes de caractères.
 */
class StringFieldType implements FieldTypeInterface
{
    public function render(mixed $value, array $options = []): string
    {
        if ($value === null) {
            return '';
        }

        $maxLength = $options['max_length'] ?? null;
        $value = htmlspecialchars((string) $value);

        if ($maxLength !== null && mb_strlen($value) > $maxLength) {
            return mb_substr($value, 0, $maxLength) . '...';
        }

        return $value;
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

