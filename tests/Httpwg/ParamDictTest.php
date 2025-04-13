<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class ParamDictTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'param-dict';
}
