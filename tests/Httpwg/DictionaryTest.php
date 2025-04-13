<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class DictionaryTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'dictionary';
}
