<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\InnerList;
use gapple\StructuredFields\Parameters;
use PHPUnit\Framework\TestCase;

class InnerListTest extends TestCase
{
    public function testDefaultParameters(): void
    {
        $item = new InnerList([]);

        $this->assertInstanceOf(Parameters::class, $item[1]);
        $this->assertEmpty(get_object_vars($item[1]));
    }

    public function testArrayAccess(): void
    {
        $list = new InnerList(
            [
                ['Test Value One', (object) []],
                ['Test Value Two', (object) []]
            ],
            (object) ['paramKey' => 'param value']
        );

        $this->assertEquals('Test Value One', $list[0][0][0]); // @phpstan-ignore-line
        $this->assertEquals('param value', $list[1]->paramKey); // @phpstan-ignore-line
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function invalidItemProvider(): array
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
     * @param mixed $value
     */
    public function testConstructInvalidItem($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new InnerList([$value]); // @phpstan-ignore-line
    }

    public function testFromArrayNestedList(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        InnerList::fromArray([new InnerList([])]);
    }
}
