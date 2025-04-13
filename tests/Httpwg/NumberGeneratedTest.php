<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class NumberGeneratedTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'number-generated';
}
