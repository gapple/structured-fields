<?php

namespace gapple\Tests\StructuredFields;

class RulesetNumberGeneratedTest extends RulesetTest
{
    protected $ruleset = 'number-generated';

    protected $skipSerializingRules = [
        '15 digit, 3 fractional small decimal' => 'PHP truncates value when parsing JSON for expected value',
        '15 digit, 3 fractional large decimal' => 'PHP rounds value when parsing JSON for expected value',
    ];
}
