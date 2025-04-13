<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class TokenTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'token';
}
