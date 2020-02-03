<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\SerializeException;
use gapple\StructuredHeaders\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeItemTest extends TestCase
{

    public function testUnkownType()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(new \stdClass());
    }
}
