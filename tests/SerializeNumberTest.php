<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\SerializeException;
use gapple\StructuredHeaders\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeNumberTest extends TestCase
{

    /**
     * Integers with more than 15 digits should fail.
     */
    public function testLargeInteger()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(1000000000000000);
    }

    /**
     * Integers with more than 15 digits should fail.
     */
    public function testLargeNegativeInteger()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(-1000000000000000);
    }

    /**
     * Decimals with more than 12 integer digits should fail.
     */
    public function testLargeDecimal()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(-1000000000000.0);
    }

    /**
     * Decimals with more than 12 integer digits should fail.
     */
    public function testLargeNegativeDecimal()
    {
        $this->expectException(SerializeException::class);

        Serializer::serializeItem(-1000000000000.0);
    }

    /**
     * Decimals should round the final digit to the nearest value, or to the
     * even value if it is equidistant.
     */
    public function testDecimalRounding()
    {
        $this->assertEquals('123.456', Serializer::serializeItem(123.4563));
        $this->assertEquals('123.456', Serializer::serializeItem(123.4565));
        $this->assertEquals('123.457', Serializer::serializeItem(123.4567));
        $this->assertEquals('123.457', Serializer::serializeItem(123.4573));
        $this->assertEquals('123.458', Serializer::serializeItem(123.4575));
        $this->assertEquals('123.458', Serializer::serializeItem(123.4577));
    }
}
