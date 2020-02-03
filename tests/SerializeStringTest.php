<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\SerializeException;
use gapple\StructuredHeaders\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeStringTest extends TestCase
{

    /**
     * Only printable ASCII is allowed in strings.
     */
    public function testInvalidCharacter()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem("🙁");
    }
}
