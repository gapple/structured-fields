<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Date;
use gapple\StructuredFields\Item;
use gapple\StructuredFields\Parser;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Additional Date parsing and serializing tests.
 */
#[CoversClass(Serializer::class)]
#[CoversClass(Parser::class)]
#[CoversClass(Date::class)]
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
