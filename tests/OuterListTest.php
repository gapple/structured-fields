<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\InnerList;
use gapple\StructuredFields\Item;
use gapple\StructuredFields\OuterList;
use PHPUnit\Framework\TestCase;

class OuterListTest extends TestCase
{
    public function testArrayAccess()
    {
        $item = new OuterList([
            ['Test Value One', (object) []],
            ['Test Value Two', (object) []],
        ]);

        $this->assertEquals('Test Value One', $item[0][0]);
    }

    public function testArrayIsset()
    {
        $item = new OuterList([
            ['Test Value One', (object) []],
            ['Test Value Two', (object) []],
        ]);

        $this->assertTrue(isset($item[0]));
        $this->assertTrue(isset($item[1]));
        $this->assertFalse(isset($item[2]));
    }

    public function testArrayAppend()
    {
        $item = new OuterList([
            ['Test Value One', (object) []],
            ['Test Value Two', (object) []],
        ]);
        $item[] = ['Test Value Three', (object) []];

        $this->assertEquals('Test Value Three', $item[2][0]);
    }

    public function testArrayOverwrite()
    {
        $item = new OuterList([
            ['Test Value One', (object) []],
            ['Test Value Two', (object) []],
        ]);
        $item[1] = ['Test Value Three', (object) []];

        $this->assertEquals('Test Value One', $item[0][0]);
        $this->assertEquals('Test Value Three', $item[1][0]);
    }

    public function testArrayUnset()
    {
        $item = new OuterList([
            ['Test Value One', (object) []],
            ['Test Value Two', (object) []],
        ]);
        unset($item[1]);

        $this->assertEquals('Test Value One', $item[0][0]);
        $this->assertEmpty($item[1]);
    }

    public function testIteration()
    {
        $listValues = [
            ['Test Value One', (object) []],
            ['Test Value Two', (object) []],
        ];
        $list = new OuterList($listValues);

        $this->assertIsIterable($list);

        $iterated = 0;
        foreach ($list as $key => $value) {
            $this->assertEquals($listValues[$key], $value);
            $iterated++;
        }
        $this->assertEquals(count($listValues), $iterated);
    }

    public function invalidItemProvider()
    {
        $items = [];

        // Bare items are not allowed, only:
        // - raw array tuples (e.g. `[42, {}]`)
        // - \gapple\StructuredFields\Item
        // - \gapple\StructuredFields\InnerList
        $items['integer'] = [42];
        $items['string'] = ['Test'];
        $items['stdClass'] = [new \stdClass()];
        $items['DateTime'] = [new \DateTime()];

        $items['array0'] = [[]];
        $items['array1'] = [[1]];
        $items['array3'] = [[1,2,3]];

        return $items;
    }

    /**
     * @dataProvider invalidItemProvider
     */
    public function testConstructInvalidItem($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        new OuterList([$value]);
    }

    /**
     * @dataProvider invalidItemProvider
     */
    public function testAppendInvalidItem($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        $list = new OuterList();
        $list[] = $value;
    }

    public function testFromArray()
    {
        $dictionary = OuterList::fromArray([
            true,
            new Item(false),
            [
                'four',
                new Item('five'),
            ],
        ]);

        $expected = new OuterList([
            new Item(true),
            new Item(false),
            new InnerList([
                new Item('four'),
                new Item('five'),
            ]),
        ]);

        $this->assertEquals($expected, $dictionary);
    }
}
