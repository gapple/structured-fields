<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\SerializingRulesetTrait;

class SerializationStringGeneratedTest extends HttpwgTestBase
{
    use SerializingRulesetTrait;

    protected static string $ruleset = 'serialisation-tests/string-generated';
}
