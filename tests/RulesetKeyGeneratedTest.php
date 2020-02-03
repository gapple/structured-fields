<?php

namespace gapple\Tests\StructuredHeaders;

class RulesetKeyGeneratedTest extends RulesetTest
{
    protected $ruleset = 'key-generated';

    protected $skipParsingRules = [
        '0x2c in dictionary key' => 'comma is valid character in dictionary',
        '0x3b in parameterised list key' => 'semicolon is valid character in parameter list',
    ];
}
