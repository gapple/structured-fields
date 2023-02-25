<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;
use PHPUnit\Framework\TestCase;

class TupleTraitTest extends TestCase
{
    public function testSetInvalidParameterValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $item = new Item('test');

        $item[1] = 'test';
    }
}
