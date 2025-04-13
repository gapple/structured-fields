<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\ParseException;
use gapple\StructuredFields\Parser;
use PHPUnit\Framework\Attributes\DataProvider;

trait ParsingRulesetTrait
{
    /**
     * @return array<string, array{Rule}>
     */
    public static function parseRulesetDataProvider(): array
    {
        return array_filter(
            static::rulesetDataProvider(),
            fn($params) => !empty($params[0]->raw)
        );
    }

    #[DataProvider('parseRulesetDataProvider')]
    public function testParsing(Rule $record): void
    {
        if (array_key_exists($record->name, $this->skipParsingRules)) {
            $this->markTestSkipped(
                'Skipped "' . $record->name . '": ' . $this->skipParsingRules[$record->name]
            );
        }

        try {
            $raw = implode(', ', $record->raw);
            $parsedValue = Parser::{'parse' . ucfirst($record->header_type)}($raw);

            if ($record->must_fail) {
                $this->fail('"' . $record->name . '" must fail parsing');
            }

            $this->assertEquals(
                $record->expected,
                $parsedValue,
                '"' . $record->name . '" was not parsed to expected value'
            );
        } catch (ParseException $e) {
            if ($record->must_fail) {
                $this->addToAssertionCount(1);
                return;
            } elseif (!$record->can_fail) {
                $this->fail('"' . $record->name . '" failed parsing with exception: ' . $e->getMessage());
            }
        }
    }
}
