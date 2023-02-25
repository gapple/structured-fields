<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;
use gapple\StructuredFields\OuterList;
use gapple\StructuredFields\Parameters;
use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeListTest extends TestCase
{
    /**
     * A list with bare tuples.
     */
    public function testUntypedListItems(): void
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

    public function testInvalidListItem(): void
    {
        $this->expectException(SerializeException::class);

        $list = [
            new Item('test'),
            'test', // Lists can't contain bare items.
        ];

        Serializer::serializeList($list); // @phpstan-ignore-line
    }

    public function testIterable(): void
    {
        $list = new \ArrayObject([
            new Item('test'),
            new Item(42),
        ]);

        $serialized = Serializer::serializeList($list);

        $this->assertEquals(
            '"test", 42',
            $serialized
        );
    }
}
