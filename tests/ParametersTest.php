<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Parameters;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
    public function testPropertyAccess(): void
    {
        $parameters = new Parameters();

        $this->assertFalse(isset($parameters->key));

        $parameters->key = 'value';
        $this->assertTrue(isset($parameters->key));
        $this->assertEquals('value', $parameters->key);

        unset($parameters->key);
        $this->assertFalse(isset($parameters->key));
    }

    public function testFromArray(): void
    {
        $parameters = Parameters::fromArray([
            'one' => true,
            'two' => 'false',
        ]);

        $this->assertTrue($parameters->one);
        $this->assertSame('false', $parameters->two);
    }
}
