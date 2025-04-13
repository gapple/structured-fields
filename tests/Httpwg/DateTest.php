<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;

class DateTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'date';
}
