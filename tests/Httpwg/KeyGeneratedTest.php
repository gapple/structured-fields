<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class KeyGeneratedTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'key-generated';
}
