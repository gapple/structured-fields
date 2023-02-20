<?php

namespace gapple\Tests\StructuredFields;

/**
 * Additional Date parsing and serializing tests.
 */
class DateTest extends RulesetTest
{
    protected function rulesetDataProvider(): array
    {
        return [
            'date - large int' => [
                Rule::fromArray([
                    'name' => 'date - large int',
                    'raw' => ['@1234567890123456'],
                    'header_type' => 'item',
                    'must_fail' => true,
                ]),
            ],
            'date - hexadecimal' => [
                Rule::fromArray([
                    'name' => 'date - hexadecimal',
                    'raw' => ['@0x62EB2779'],
                    'header_type' => 'item',
                    'must_fail' => true,
                ]),
            ],
        ];
    }
}
