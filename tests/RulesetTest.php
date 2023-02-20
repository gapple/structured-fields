<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\ParseException;
use gapple\StructuredFields\Parser;
use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\TestCase;

abstract class RulesetTest extends TestCase
{
    /**
     * An array of rules which should skip the parsing test.
     *
     * The element key should be the name of the rule, and the value should be
     * the message to provide for skipping the rule.
     *
     * @var array<string, string>
     */
    protected $skipParsingRules = [];

    /**
     * An array of rules which should skip the serializing test.
     *
     * The element key should be the name of the rule, and the value should be
     * the message to provide for skipping the rule.
     *
     * @var array<string, string>
     */
    protected $skipSerializingRules = [];

    /**
     * @return array<string, array{Rule}>
     */
    abstract protected function rulesetDataProvider(): array;

    /**
     * @return array<string, array{Rule}>
     */
    public function parseRulesetDataProvider(): array
    {
        $tests = array_filter(
            static::rulesetDataProvider(),
            function ($params) {
                return !empty($params[0]->raw);
            }
        );

        if (empty($tests)) {
            $this->markTestSkipped("No parse rules");
        }

        return $tests;
    }

    /**
     * @return array<string, array{Rule}>
     */
    public function serializeRulesetDataProvider(): array
    {
        $tests = array_filter(
            static::rulesetDataProvider(),
            function ($params) {
                return !empty($params[0]->expected);
            }
        );

        if (empty($tests)) {
            $this->markTestSkipped("No serialize rules");
        }

        return $tests;
    }

    /**
     * @dataProvider parseRulesetDataProvider
     * @param Rule $record
     */
    public function testParsing(Rule $record): void
    {
        if (array_key_exists($record->name, $this->skipParsingRules)) {
            $this->markTestSkipped(
                'Skipped "' . $record->name . '": ' . $this->skipParsingRules[$record->name]
            );
        }

        try {
            $raw = implode(',', $record->raw);
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
                $this->fail('"' . $record->name . '" must not fail parsing');
            }
        }
    }

    /**
     * @dataProvider serializeRulesetDataProvider
     * @param Rule $record
     */
    public function testSerializing(Rule $record): void
    {
        if (array_key_exists($record->name, $this->skipSerializingRules)) {
            $this->markTestSkipped(
                'Skipped "' . $record->name . '": ' . $this->skipSerializingRules[$record->name]
            );
        }

        try {
            $serializedValue = Serializer::{'serialize' . ucfirst($record->header_type)}($record->expected);

            if ($record->must_fail) {
                $this->fail('"' . $record->name . '" must fail serializing');
            }

            $this->assertEquals(
                implode(',', $record->canonical ?? $record->raw),
                $serializedValue,
                '"' . $record->name . '" was not serialized to expected value'
            );
        } catch (SerializeException $e) {
            if ($record->must_fail) {
                $this->addToAssertionCount(1);
                return;
            } else {
                $this->fail('"' . $record->name . '"  failed serializing');
            }
        }
    }
}
