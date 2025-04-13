<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class ListListTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'listlist';
}
