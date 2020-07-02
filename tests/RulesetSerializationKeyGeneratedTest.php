<?php

namespace gapple\Tests\StructuredFields;

class RulesetSerializationKeyGeneratedTest extends RulesetTest
{
    protected $ruleset = 'serialisation-tests/key-generated';

    protected $skipSerializingRules = [
        '0x00 in dictionary key - serialise only' => "PHP can't parse JSON with null bytes in object keys",
        '0x00 starting an dictionary key - serialise only' => "PHP can't parse JSON with null bytes in object keys",
        '0x00 in parameterised list key - serialise only' => "PHP can't parse JSON with null bytes in object keys",
        '0x00 starting a parameterised list key' => "PHP can't parse JSON with null bytes in object keys",
    ];
}
