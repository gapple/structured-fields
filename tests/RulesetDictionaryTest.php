<?php

namespace gapple\Tests\StructuredHeaders;

class RulesetDictionaryTest extends RulesetTest
{
    protected $ruleset = 'dictionary';

    protected $skipParsingRules = [
        'duplicate key dictionary' => 'Duplicate dictionary keys should overwrite previous value.',
    ];

    protected $skipSerializingRules = [
        'duplicate key dictionary' => 'Duplicate dictionary keys should overwrite previous value.',
    ];
}
