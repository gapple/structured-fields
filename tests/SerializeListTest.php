<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\InnerList;
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

    public function testArray(): void
    {
        $list = [
            new Item('test'),
            new Item(42),
        ];

        $serialized = Serializer::serializeList($list);

        $this->assertEquals(
            '"test", 42',
            $serialized
        );
    }

    public function testInnerList(): void
    {
        $list = [
            InnerList::fromArray(["test"]),
            new Item(42),
        ];

        $serialized = Serializer::serializeList($list);

        $this->assertEquals(
            '("test"), 42',
            $serialized
        );
    }

    public function testNestedInnerListTuple(): void
    {
        // InnerList object validates its values, so use array format tuples.
        $list = [
            [ // Outer Inner List Tuple
                [ // Outer Inner Items
                    [  // Inner Inner List Tuple
                        [ // Inner Inner List Items
                            new Item("test"),
                            new Item(23),
                        ],
                        new \stdClass(), // Inner Inner List Parameters
                    ],
                    new Item(42),
                ],
                new \stdClass(), // Outer Inner Parameters
            ],
            new Item(42), // List Parameters
        ];

        $this->expectException(SerializeException::class);
        $this->expectExceptionMessage("Inner lists cannot be nested");
        Serializer::serializeList($list);
    }

    public function testNestedInnerListObject(): void
    {
        // InnerList object validates its values, so use array format tuple for first layer inner list.
        $list = [
            [ // Outer Inner List Tuple
                [ // Outer Inner Items
                    new InnerList([new Item("test")], Parameters::fromArray(["p" => true])),
                ],
                new \stdClass(), // Outer Inner Parameters
            ],
            new Item(42), // List Parameters
        ];

        $this->expectException(SerializeException::class);
        $this->expectExceptionMessage("Inner lists cannot be nested");
        Serializer::serializeList($list);
    }
}
