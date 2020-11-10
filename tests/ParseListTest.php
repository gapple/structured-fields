<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Parser;
use PHPUnit\Framework\TestCase;

class ParseListTest extends TestCase
{

    public function multipleStringProvider()
    {
        $dataset = [];

        $dataset[] = [
            'raw' => '"one", 1, 42;towel;panic=?0, "two"',
            'expected' => [
                ['one', (object) []],
                [1, (object) []],
                [42, (object) ['towel' => true, 'panic' => false]],
                ['two', (object) []],
            ]
        ];

        $dataset[] = [
            'raw' => '"\"Not\\\A;Brand";v="99", "Chromium";v="86"',
            'expected' => [
                ['"Not\\A;Brand', (object) ['v' => "99"]],
                ['Chromium', (object) ['v' => "86"]],
            ],
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
