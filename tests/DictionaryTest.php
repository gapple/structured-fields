<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Dictionary;
use gapple\StructuredFields\InnerList;
use gapple\StructuredFields\Item;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
    public function testPropertyAccess(): void
    {
        $dictionary = new Dictionary();

        $this->assertFalse(isset($dictionary->key));

        $dictionary->key = ['value', (object) []];
        $this->assertTrue(isset($dictionary->key));
        $this->assertEquals('value', $dictionary->key[0]);

        unset($dictionary->key);
        $this->assertFalse(isset($dictionary->key));
    }

    public function testFromArray(): void
    {
        $dictionary = Dictionary::fromArray([
            'one' => true,
            'two' => new Item(false),
            'three' => [
                'four',
                new Item('five'),
            ],
        ]);

        $expected = new Dictionary();
        $expected->one = new Item(true);
        $expected->two = new Item(false);
        $expected->three = new InnerList([
            new Item('four'),
            new Item('five'),
        ]);

        $this->assertEquals($expected, $dictionary);
    }
}
