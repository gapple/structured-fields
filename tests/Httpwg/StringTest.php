<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\ParsingRulesetTrait;
use gapple\Tests\StructuredFields\SerializingRulesetTrait;

class StringTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;
    use SerializingRulesetTrait;

    protected static string $ruleset = 'string';
}
