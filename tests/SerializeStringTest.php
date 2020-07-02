<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
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
