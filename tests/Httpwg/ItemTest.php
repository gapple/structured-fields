<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class ItemTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'item';
}
