<?php

namespace gapple\Tests\StructuredHeaders;

class RulesetDictionaryTest extends RulesetTest
{
    protected $ruleset = 'dictionary';

    protected $skipRules = [
        'duplicate key dictionary' => 'Duplicate dictionary keys should overwrite previous value.',
    ];
}
