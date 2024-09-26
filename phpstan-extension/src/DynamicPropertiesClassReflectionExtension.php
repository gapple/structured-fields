<?php

namespace gapple\StructuredFields\PHPStan;

use gapple\StructuredFields\Dictionary;
use gapple\StructuredFields\Parameters;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

class DynamicPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return (
                $classReflection->is(Dictionary::class)
                || $classReflection->is(Parameters::class)
            )
            && preg_match('/^[a-z*][a-z0-9.*_-]*$/', $propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new DynamicPropertyReflection($classReflection);
    }
}
