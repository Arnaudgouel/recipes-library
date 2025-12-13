<?php

namespace App\DataTable\Type\Field;

/**
 * Type de champ pour les entités associées.
 */
class EntityFieldType implements FieldTypeInterface
{
    public function render(mixed $value, array $options = []): string
    {
        if ($value === null) {
            return $options['null_value'] ?? '';
        }

        if (!is_object($value)) {
            return '';
        }

        // Si une méthode de rendu personnalisée est fournie
        if (isset($options['render_callback']) && is_callable($options['render_callback'])) {
            return call_user_func($options['render_callback'], $value);
        }

        // Essayer __toString()
        if (method_exists($value, '__toString')) {
            return htmlspecialchars((string) $value);
        }

        // Essayer getNom() ou getName()
        if (method_exists($value, 'getNom')) {
            return htmlspecialchars($value->getNom());
        }
        if (method_exists($value, 'getName')) {
            return htmlspecialchars($value->getName());
        }

        // Essayer getId()
        if (method_exists($value, 'getId')) {
            return (string) $value->getId();
        }

        return get_class($value);
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

