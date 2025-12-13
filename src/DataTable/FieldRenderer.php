<?php

namespace App\DataTable;

use App\DataTable\Config\FieldConfig;
use App\DataTable\Type\Field\FieldTypeRegistry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Service de rendu des champs.
 */
class FieldRenderer
{
    public function __construct(
        private readonly FieldTypeRegistry $fieldTypeRegistry,
        private readonly PropertyAccessorInterface $propertyAccessor
    ) {
    }

    public function render(mixed $entity, string $fieldName, FieldConfig $fieldConfig): string
    {
        try {
            $value = $this->propertyAccessor->getValue($entity, $fieldName);
        } catch (\Exception $e) {
            return '';
        }

        $fieldType = $this->fieldTypeRegistry->get($fieldConfig->type);
        if ($fieldType !== null) {
            return $fieldType->render($value, $fieldConfig->options);
        }

        $stringType = $this->fieldTypeRegistry->get('string');
        return $stringType ? $stringType->render($value) : (string) $value;
    }
}

