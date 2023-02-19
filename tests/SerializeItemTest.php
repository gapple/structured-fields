<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;
use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeItemTest extends TestCase
{
    public function testUnknownType(): void
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new \stdClass());
    }

    public function testNullValueItem(): void
    {
        $this->expectException(SerializeException::class);
        $this->expectExceptionMessage('Unrecognized type');

        Serializer::serializeItem(new Item(null));
    }

    public function testNoParameters(): void
    {
        $item = new Item(true);

        $result = Serializer::serializeItem($item);

        $this->assertEquals('?1', $result);
    }

    public function testItemObjectWithParameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $parameters = new \stdClass();
        $item = new Item(true, $parameters);

        Serializer::serializeItem($item, $parameters);
    }
}
