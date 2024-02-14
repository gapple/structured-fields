<?php

namespace gapple\StructuredFields\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class DynamicPropertyReflection implements PropertyReflection
{
    /**
     * @var ClassReflection
     */
    private $classReflection;

    public function __construct(ClassReflection $classReflection)
    {
        $this->classReflection = $classReflection;
    }

    public function getDeclaringClass(): \PHPStan\Reflection\ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getReadableType(): Type
    {
        return new MixedType();
    }

    public function getWritableType(): Type
    {
        return new MixedType();
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return true;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
}
