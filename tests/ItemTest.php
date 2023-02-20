<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;
use gapple\StructuredFields\Parameters;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    public function testDefaultParameters(): void
    {
        $item = new Item(true);

        $this->assertInstanceOf(Parameters::class, $item[1]);
        $this->assertEmpty(get_object_vars($item[1]));
    }

    public function testPropertyAccess(): void
    {
        $item = new Item('Test Value', (object) ['paramKey' => 'param value']);

        $this->assertEquals('Test Value', $item->getValue());
        $this->assertEquals('param value', $item->getParameters()->paramKey); // @phpstan-ignore-line
    }

    public function testArrayAccess(): void
    {
        $item = new Item('Test Value', (object) ['paramKey' => 'param value']);

        $this->assertEquals('Test Value', $item[0]);
        $this->assertEquals('param value', $item[1]->paramKey); // @phpstan-ignore-line
    }

    public function testArraySet(): void
    {
        $item = new Item('Test Value', (object) ['paramKey' => 'param value']);

        $item[0] = 'Modified Value';
        $item[1] = (object) ['paramKey' => 'Modified param value'];
        $this->assertEquals('Modified Value', $item[0]);
        $this->assertEquals('Modified param value', $item[1]->paramKey); // @phpstan-ignore-line
    }

    public function testArrayIndexIsset(): void
    {
        $item = new Item(true);

        $this->assertTrue(isset($item[0]));
        $this->assertTrue(isset($item[1]));
        $this->assertFalse(isset($item[2])); // @phpstan-ignore-line
    }

    public function testArrayOutOfBounds(): void
    {
        $item = new Item(true);

        $this->assertEmpty($item[2]); // @phpstan-ignore-line
    }

    public function testArrayUnset(): void
    {
        $item = new Item('Test Value', (object) ['paramKey' => 'param value']);

        unset($item[0]);
        unset($item[1]);

        $this->assertEmpty($item[0]);
        $this->assertEquals(new Parameters(), $item[1]);
    }
}
