<?php

namespace App\DataTable\Type\Field;

/**
 * Type de champ pour les dates et heures.
 */
class DateTimeFieldType implements FieldTypeInterface
{
    public function render(mixed $value, array $options = []): string
    {
        if ($value === null) {
            return $options['null_value'] ?? '';
        }

        if (!$value instanceof \DateTimeInterface) {
            return '';
        }

        $format = $options['format'] ?? 'd/m/Y H:i';
        return $value->format($format);
    }

    public function supports(string $type): bool
    {
        return in_array($type, ['datetime', 'date', 'datetime_immutable', 'date_immutable'], true);
    }

    public function getName(): string
    {
        return 'datetime';
    }
}

