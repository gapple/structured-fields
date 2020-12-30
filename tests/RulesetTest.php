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
     * @var array
     */
    protected $skipParsingRules = [];

    /**
     * An array of rules which should skip the serializing test.
     *
     * The element key should be the name of the rule, and the value should be
     * the message to provide for skipping the rule.
     *
     * @var array
     */
    protected $skipSerializingRules = [];

    abstract protected function rulesetDataProvider(): array;

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
     *
     * @param $record
     */
    public function testParsing($record)
    {
        if (array_key_exists($record->name, $this->skipParsingRules)) {
            $this->markTestSkipped(
                'Skipped ' . $this->ruleset . ' "' . $record->name . '": ' . $this->skipParsingRules[$record->name]
            );
        }

        try {
            $raw = implode(',', $record->raw);
            $parsedValue = Parser::{'parse' . ucfirst($record->header_type)}($raw);

            if ($record->must_fail) {
                $this->fail($this->ruleset . ' "' . $record->name . '" must fail parsing');
            }

            $this->assertEquals(
                $record->expected,
                $parsedValue,
                $this->ruleset . ' "' . $record->name . '" was not parsed to expected value'
            );
        } catch (ParseException $e) {
            if ($record->must_fail) {
                $this->addToAssertionCount(1);
                return;
            } elseif (!$record->can_fail) {
                $this->fail($this->ruleset . ' "' . $record->name . '" must not fail parsing');
            }
        }
    }

    /**
     * @dataProvider serializeRulesetDataProvider
     *
     * @param $record
     */
    public function testSerializing($record)
    {
        if (array_key_exists($record->name, $this->skipSerializingRules)) {
            $this->markTestSkipped(
                'Skipped ' . $this->ruleset . ' "' . $record->name . '": ' . $this->skipSerializingRules[$record->name]
            );
        }

        try {
            $serializedValue = Serializer::{'serialize' . ucfirst($record->header_type)}($record->expected);

            if ($record->must_fail) {
                $this->fail($this->ruleset . ' "' . $record->name . '" must fail serializing');
            }

            $this->assertEquals(
                implode(',', $record->canonical ?? $record->raw),
                $serializedValue,
                $this->ruleset . ' "' . $record->name . '" was not serialized to expected value'
            );
        } catch (SerializeException $e) {
            if ($record->must_fail) {
                $this->addToAssertionCount(1);
                return;
            } else {
                $this->fail($this->ruleset . ' "' . $record->name . '"  failed serializing');
            }
        }
    }
}
