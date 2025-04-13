<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class DisplayStringTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'display-string';
}
