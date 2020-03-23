<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\SerializeException;
use gapple\StructuredHeaders\Serializer;
use gapple\StructuredHeaders\Token;
use PHPUnit\Framework\TestCase;

class SerializeKeyTest extends TestCase
{

    public function testUpperCharacter()
    {
        $this->expectException(SerializeException::class);

        $dictionary = (object) [
            'aBc' => [1, new \stdClass()],
        ];

        Serializer::serializeDictionary($dictionary);
    }

    public function invalidFirstCharacterDataProvider()
    {
        return [
            'number' => ['1'],
            'underscore' => ['_'],
            'dash' => ['-'],
            'period' => ['.'],
        ];
    }

    /**
     * @dataProvider invalidFirstCharacterDataProvider
     */
    public function testInvalidFirstCharacter($character)
    {
        $this->expectException(SerializeException::class);

        $dictionary = (object) [
            $character . 'abc123' => [1, new \stdClass()],
        ];

        Serializer::serializeDictionary($dictionary);
    }
}
