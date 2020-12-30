<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\InnerList;
use PHPUnit\Framework\TestCase;

class InnerListTest extends TestCase
{
    public function testDefaultParameters()
    {
        $item = new InnerList([]);

        $this->assertInstanceOf(\stdClass::class, $item[1]);
        $this->assertEmpty(get_object_vars($item[1]));
    }

    public function testArrayAccess()
    {
        $list = new InnerList(
            [
                ['Test Value One', (object) []],
                ['Test Value Two', (object) []]
            ],
            (object) ['paramKey' => 'param value']
        );

        $this->assertEquals('Test Value One', $list[0][0][0]);
        $this->assertEquals('param value', $list[1]->paramKey);
    }

    public function invalidItemProvider()
    {
        $items = [];

        // Bare items are not allowed, only:
        // - raw array tuples (e.g. `[42, {}]`)
        // - \gapple\StructuredFields\Item
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

        new InnerList([$value]);
    }
}
