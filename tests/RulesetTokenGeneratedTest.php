<?php

namespace gapple\Tests\StructuredHeaders;

class RulesetTokenGeneratedTest extends RulesetTest
{
    protected $ruleset = 'token-generated';

    protected $skipSerializingRules = [
        '0x3b in token' => 'Serializing parameters is not yet implemented',
    ];
}
