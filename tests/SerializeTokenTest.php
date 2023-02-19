<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use gapple\StructuredFields\Token;
use PHPUnit\Framework\TestCase;

class SerializeTokenTest extends TestCase
{
    public function testNumericFirstCharacter(): void
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new Token('123abc'));
    }

    /**
     * Test a symbol that is allowed after the first character of a token.
     */
    public function testSymbolFirstCharacter(): void
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new Token('$123abc'));
    }

    public function testInvalidCharacter(): void
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new Token('@'));
    }
}
