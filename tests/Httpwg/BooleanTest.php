<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class BooleanTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'boolean';
}
