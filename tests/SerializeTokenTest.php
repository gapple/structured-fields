<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use gapple\StructuredFields\Token;
use PHPUnit\Framework\TestCase;

class SerializeTokenTest extends TestCase
{
    public function testNumericFirstCharacter()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new Token('123abc'));
    }

    /**
     * Test a symbol that is allowed after the first character of a token.
     */
    public function testSymbolFirstCharacter()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new Token('$123abc'));
    }

    public function testInvalidCharacter()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new Token('@'));
    }
}
