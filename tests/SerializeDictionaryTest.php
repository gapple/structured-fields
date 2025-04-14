<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Serializer::class)]
class SerializeDictionaryTest extends TestCase
{
    public function testUntyped(): void
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
