<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    public function testDefaultParameters()
    {
        $item = new Item(true);

        $this->assertInstanceOf(\stdClass::class, $item[1]);
        $this->assertEmpty(get_object_vars($item[1]));
    }

    public function testArrayAccess()
    {
        $item = new Item('Test Value', (object) ['paramKey' => 'param value']);

        $this->assertEquals('Test Value', $item[0]);
        $this->assertEquals('param value', $item[1]->paramKey);
    }

    public function testArraySet()
    {
        $item = new Item('Test Value', (object) ['paramKey' => 'param value']);

        $item[0] = 'Modified Value';
        $item[1] = (object) ['paramKey' => 'Modified param value'];
        $this->assertEquals('Modified Value', $item[0]);
        $this->assertEquals('Modified param value', $item[1]->paramKey);
    }

    public function testArrayIndexIsset()
    {
        $item = new Item(true);

        $this->assertTrue(isset($item[0]));
        $this->assertTrue(isset($item[1]));
        $this->assertFalse(isset($item[2]));
    }

    public function testArrayOutOfBounds()
    {
        $item = new Item(true);

        $this->assertEmpty($item[2]);
    }

    public function testArrayUnset()
    {
        $item = new Item('Test Value', (object) ['paramKey' => 'param value']);

        unset($item[0]);
        unset($item[1]);

        $this->assertEmpty($item[0]);
        $this->assertEquals(new \stdClass(), $item[1]);
    }
}
