<?php

namespace App\DataTable\Type\Field;

/**
 * Registre des types de champs disponibles.
 */
class FieldTypeRegistry
{
    /** @var FieldTypeInterface[] */
    private array $types = [];

    public function __construct()
    {
        $this->register(new StringFieldType());
        $this->register(new BooleanFieldType());
        $this->register(new DateTimeFieldType());
        $this->register(new EntityFieldType());
        $this->register(new ChoiceFieldType());
    }

    public function register(FieldTypeInterface $type): void
    {
        $this->types[$type->getName()] = $type;
    }

    public function get(string $name): ?FieldTypeInterface
    {
        return $this->types[$name] ?? null;
    }

    public function findForType(string $type): ?FieldTypeInterface
    {
        foreach ($this->types as $fieldType) {
            if ($fieldType->supports($type)) {
                return $fieldType;
            }
        }

        return $this->types['string'] ?? null;
    }

    /**
     * @return FieldTypeInterface[]
     */
    public function all(): array
    {
        return $this->types;
    }
}

