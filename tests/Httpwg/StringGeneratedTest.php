<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class StringGeneratedTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'string-generated';
}
