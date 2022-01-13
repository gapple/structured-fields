<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;
use gapple\StructuredFields\OuterList;
use gapple\StructuredFields\Parameters;
use gapple\StructuredFields\Parser;
use PHPUnit\Framework\TestCase;

class ParseListTest extends TestCase
{
    public function multipleStringProvider()
    {
        $dataset = [];

        $dataset[] = [
            'raw' => '"one", 1, 42;towel;panic=?0, "two"',
            'expected' => new OuterList([
                new Item('one'),
                new Item(1),
                new Item(42, Parameters::fromArray(['towel' => true, 'panic' => false])),
                new Item('two'),
            ])
        ];

        $dataset[] = [
            'raw' => '"\"Not\\\A;Brand";v="99", "Chromium";v="86"',
            'expected' => new OuterList([
                new Item('"Not\\A;Brand', Parameters::fromArray(['v' => "99"])),
                new Item('Chromium', Parameters::fromArray(['v' => "86"])),
            ]),
        ];

        return $dataset;
    }

    /**
     * @dataProvider multipleStringProvider
     */
    public function testListWithMultipleStrings($raw, $expected)
    {
        $this->assertEquals($expected, Parser::parseList($raw));
    }
}
