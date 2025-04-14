<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Serializer::class)]
class SerializeStringTest extends TestCase
{
    /**
     * Only printable ASCII is allowed in strings.
     */
    public function testInvalidCharacter(): void
    {
        $this->expectException(SerializeException::class);
        $this->expectExceptionMessage("Invalid characters in string");

        Serializer::serializeItem("🙁");
    }

    public function testStringableObject(): void
    {
        $stringable = new class {
            public function __toString(): string
            {
                return "Don't Panic";
            }
        };

        $this->assertEquals('"Don\'t Panic"', Serializer::serializeItem($stringable));
    }

    public function testInvalidStringableObject(): void
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
