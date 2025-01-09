<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\SerializingRulesetTrait;

class SerializationKeyGeneratedTest extends HttpwgTestBase
{
    use SerializingRulesetTrait;

    protected static string $ruleset = 'serialisation-tests/key-generated';
}
