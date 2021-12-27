<?php

declare(strict_types=1);

namespace EventSauce\ObjectHydrator;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

use function count;
use function enum_exists;

class PropertyType
{
    private array $concreteTypes;

    private function __construct(ConcreteType ... $concreteTypes)
    {
        $this->concreteTypes = $concreteTypes;
    }

    public function canBeMapped(): bool
    {
        return count($this->concreteTypes) === 1 && $this->concreteTypes[0]->isBuiltIn === false;
    }

    public static function fromNamedType(ReflectionNamedType $type): static
    {
        return new static(new ConcreteType($type->getName(), $type->isBuiltin()));
    }

    public static function fromCompositeType(ReflectionIntersectionType|ReflectionUnionType $type)
    {
        /** @var ReflectionNamedType[] $types */
        $types = $type->getTypes();
        $resolvedTypes = [];

        foreach ($types as $type) {
            $resolvedTypes[] = new ConcreteType($type->getName(), $type->isBuiltin());
        }

        return new PropertyType(...$resolvedTypes);
    }

    public static function mixed(): static
    {
        return new static(new ConcreteType('mixed', true));
    }

    public function firstTypeName(): string
    {
        return $this->concreteTypes[0]->name;
    }

    public function isEnum(): bool
    {
        return count($this->concreteTypes) === 1 && enum_exists($this->concreteTypes[0]->name);
    }
}