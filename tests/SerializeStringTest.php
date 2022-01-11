<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeStringTest extends TestCase
{
    /**
     * Only printable ASCII is allowed in strings.
     */
    public function testInvalidCharacter()
    {
        $this->expectException(SerializeException::class);
        $this->expectExceptionMessage("Invalid characters in string");

        Serializer::serializeItem("🙁");
    }

    public function testStringableObject()
    {
        $stringable = new class {
            public function __toString(): string
            {
                return "Don't Panic";
            }
        };

        $this->assertEquals('"Don\'t Panic"', Serializer::serializeItem($stringable));
    }

    public function testInvalidStringableObject()
    {
        $this->expectException(SerializeException::class);
        $this->expectExceptionMessage("Invalid characters in string");

        $stringable = new class {
            public function __toString(): string
            {
                return "🙁";
            }
        };

        Serializer::serializeItem($stringable);
    }
}
