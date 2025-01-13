<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Item;

/**
 * Additional Date parsing and serializing tests.
 */
class DateTest extends RulesetTestBase
{
    use ParsingRulesetTrait;
    use SerializingRulesetTrait;

    /**
     * {@inheritdoc}
     */
    protected static function rulesetDataProvider(): array
    {
        return [
            'large int' => [
                Rule::fromArray([
                    'name' => 'date - large int',
                    'raw' => ['@1234567890123456'],
                    'header_type' => 'item',
                    'must_fail' => true,
                ]),
            ],
            'hexadecimal' => [
                Rule::fromArray([
                    'name' => 'date - hexadecimal',
                    'raw' => ['@0x62EB2779'],
                    'header_type' => 'item',
                    'must_fail' => true,
                ]),
            ],
            // Serialize any \DateTimeInterface object.
            'DateTimeInterface' => [
              Rule::fromArray([
                  'name' => 'date - DateTimeInterface',
                  'header_type' => 'item',
                  'expected' => new Item(new \DateTimeImmutable('@629528400')),
                  'canonical' => ['@629528400'],
              ]),
            ],
        ];
    }
}
