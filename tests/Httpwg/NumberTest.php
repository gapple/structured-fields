<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class NumberTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'number';
}
