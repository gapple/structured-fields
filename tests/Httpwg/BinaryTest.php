<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class BinaryTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'binary';
}
