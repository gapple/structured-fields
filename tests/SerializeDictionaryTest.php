<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;
use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeDictionaryTest extends TestCase
{
    public function testUntyped()
    {
        $dictionary = (object) [
            'one' => [true, (object) []],
            'two' => ['three', (object) ['param' => 'value']],
        ];

        $this->assertEquals(
            'one, two="three";param="value"',
            Serializer::serializeDictionary($dictionary)
        );
    }
}
