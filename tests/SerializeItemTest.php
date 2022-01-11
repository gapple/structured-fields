<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeItemTest extends TestCase
{
    public function testUnkownType()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new \stdClass());
    }
}
