<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Dictionary;
use gapple\StructuredFields\Parameters;
use PhpParser\Node\Param;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
    public function testPropertyAccess()
    {
        $parameters = new Parameters();

        $this->assertFalse(isset($parameters->key));

        $parameters->key = 'value';
        $this->assertTrue(isset($parameters->key));
        $this->assertEquals('value', $parameters->key);

        unset($parameters->key);
        $this->assertFalse(isset($parameters->key));
    }

    public function testFromArray()
    {
        $parameters = Parameters::fromArray([
            'one' => true,
            'two' => 'false',
        ]);

        $this->assertSame(true, $parameters->one);
        $this->assertSame('false', $parameters->two);
    }
}
