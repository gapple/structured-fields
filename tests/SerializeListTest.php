<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\OuterList;
use gapple\StructuredFields\Parameters;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeListTest extends TestCase
{
    /**
     * A list with bare tuples.
     */
    public function testUntypedList(): void
    {
        $itemParam = new \stdClass();
        $itemParam->item_param = 32;

        $value = new OuterList([
            ['value1', $itemParam],
            ['value2', (object) []],
            [
                [
                    ['listvalue1', (object) []],
                    ['listvalue2', (object) []],
                ],
                Parameters::fromArray(['list-param' => 'value']),
            ],
        ]);

        $serialized = Serializer::serializeList($value);

        $this->assertEquals(
            '"value1";item_param=32, "value2", ("listvalue1" "listvalue2");list-param="value"',
            $serialized
        );
    }
}
