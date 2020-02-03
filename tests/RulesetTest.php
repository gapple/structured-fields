<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\Bytes;
use gapple\StructuredHeaders\ParseException;
use gapple\StructuredHeaders\Parser;
use gapple\StructuredHeaders\SerializeException;
use gapple\StructuredHeaders\Serializer;
use gapple\StructuredHeaders\Token;
use ParagonIE\ConstantTime\Base32;
use PHPUnit\Framework\TestCase;

abstract class RulesetTest extends TestCase
{
    protected $ruleset;

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

    public function rulesetDataProvider()
    {
        $path = __DIR__ . '/../vendor/httpwg/structured-header-tests/' . $this->ruleset . '.json';
        if (!file_exists($path)) {
            throw new \RuntimeException('Ruleset file does not exist');
        }

        $rules = json_decode(file_get_contents($path));

        $dataset = [];
        foreach ($rules as $rule) {
            if (isset($rule->expected)) {
                if ($rule->header_type == 'item') {
                    self::convertItemValue($rule->expected);
                } elseif ($rule->header_type == 'list') {
                    self::convertListValues($rule->expected);
                } elseif ($rule->header_type == 'dictionary') {
                    self::convertDictionaryValues($rule->expected);
                }
            }

            $dataset[$rule->name] = [$rule];
        }

        return $dataset;
    }

    public function serializeRulesetDataProvider()
    {
        return array_filter(
            self::rulesetDataProvider(),
            function ($params) {
                return !empty($params[0]->expected);
            }
        );
    }

    /**
     * @dataProvider rulesetDataProvider
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

        // Set default values for optional keys.
        $record->must_fail = $record->must_fail ?? false;
        $record->can_fail = $record->can_fail ?? false;

        try {
            $raw = implode(',', $record->raw);
            if ($record->header_type == 'item') {
                $parsedValue = Parser::parseItem($raw);
            } elseif ($record->header_type == 'list') {
                $parsedValue = Parser::parseList($raw);
            } elseif ($record->header_type == 'dictionary') {
                $parsedValue = Parser::parseDictionary($raw);
            } else {
                $this->markTestSkipped($this->ruleset . ' "' . $record->name . ' Unrecognized header type');
            }

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
            if ($record->header_type == 'item') {
                $serializedValue = Serializer::serializeItem($record->expected[0], $record->expected[1]);
            } elseif ($record->header_type == 'list') {
                $serializedValue = Serializer::serializeList($record->expected);
                $this->expectNotToPerformAssertions();
                return;
            } elseif ($record->header_type == 'dictionary') {
                $serializedValue = Serializer::serializeDictionary($record->expected);
                $this->expectNotToPerformAssertions();
                return;
            } else {
                $this->markTestSkipped($this->ruleset . ' "' . $record->name . ' Unrecognized header type');
            }

            $this->assertEquals(
                implode(',', $record->canonical ?? $record->raw),
                $serializedValue,
                $this->ruleset . ' "' . $record->name . '" was not serialized to expected value'
            );
        } catch (SerializeException $e) {
            $this->fail($this->ruleset . ' "' . $record->name . '"  failed serializing');
        }
    }

    private static function convertItemValue(&$value)
    {
        if ($value[0] instanceof \stdClass) {
            self::convertValue($value[0]);
        }

        foreach (get_object_vars($value[1]) as $paramKey => &$paramValue) {
            if ($paramValue instanceof \stdClass) {
                self::convertValue($value[1]->{$paramKey});
            }
        }
    }

    private static function convertValue(&$value)
    {
        if ($value->__type == 'token') {
            $value = new Token($value->value);
        } elseif ($value->__type == 'binary') {
            $value = new Bytes(Base32::decodeUpper($value->value));
        }
    }

    private static function convertListValues(&$list)
    {
        foreach ($list as &$item) {
            if (end($item) instanceof \stdClass) {
                self::convertItemValue($item);
            } else {
                self::convertListValues($item);
            }
        }
    }

    private static function convertDictionaryValues(&$dictionary)
    {
        foreach (get_object_vars($dictionary) as $key => $item) {
            if (end($item) instanceof \stdClass) {
                self::convertItemValue($dictionary->{$key});
            } else {
                self::convertListValues($dictionary->{$key});
            }
        }
    }
}
