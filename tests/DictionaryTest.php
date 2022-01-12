<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Dictionary;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
    public function testPropertyAccess()
    {
        $dictionary = new Dictionary();

        $this->assertFalse(isset($dictionary->key));

        $dictionary->key = ['value', (object) []];
        $this->assertTrue(isset($dictionary->key));
        $this->assertEquals('value', $dictionary->key[0]);

        unset($dictionary->key);
        $this->assertFalse(isset($dictionary->key));
    }
}
