<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class ParamListTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'param-list';
}
