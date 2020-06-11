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

    private function rulesetDataProvider()
    {
        $path = __DIR__ . '/../vendor/httpwg/structured-field-tests/' . $this->ruleset . '.json';
        if (!file_exists($path)) {
            throw new \RuntimeException('Ruleset file does not exist');
        }

        $rulesJson = file_get_contents($path);
        // PHP doesn't allow null bytes in object keys, and will fail parsing.
        // Corresponding tests will need to be ignored in relevant test case.
        $rulesJson = preg_replace(
            '/".*?\\\u0000.*?":/',
            '"":',
            $rulesJson
        );

        $rules = json_decode($rulesJson);
        if (is_null($rules) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Unable to parse ruleset JSON file.");
        }

        $dataset = [];
        foreach ($rules as $rule) {
            if (isset($rule->expected)) {
                self::convertValues($rule->expected);
            }

            // Set default values for optional keys.
            $rule->must_fail = $rule->must_fail ?? false;
            $rule->can_fail = $rule->can_fail ?? false;

            $dataset[$rule->name] = [$rule];
        }

        return $dataset;
    }

    public function parseRulesetDataProvider()
    {
        return array_filter(
            static::rulesetDataProvider(),
            function ($params) {
                return !empty($params[0]->raw);
            }
        );
    }

    public function serializeRulesetDataProvider()
    {
        return array_filter(
            static::rulesetDataProvider(),
            function ($params) {
                return !empty($params[0]->expected);
            }
        );
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
            } elseif ($record->header_type == 'dictionary') {
                $serializedValue = Serializer::serializeDictionary($record->expected);
            } else {
                $this->markTestSkipped($this->ruleset . ' "' . $record->name . ' Unrecognized header type');
            }

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

    /**
     * Convert any encoded special values to typed objects.
     *
     * @param $input
     *   The expected Item, List, or Dictionary structure.
     */
    private static function convertValues(&$input)
    {
        if (is_array($input)) {
            foreach ($input as &$value) {
                self::convertValues($value);
            }
        } elseif (is_object($input)) {
            if (property_exists($input, '__type')) {
                if ($input->__type == 'token') {
                    $input = new Token($input->value);
                } elseif ($input->__type == 'binary') {
                    $input = new Bytes(Base32::decodeUpper($input->value));
                }
            } else {
                foreach (get_object_vars($input) as $paramKey => $paramValue) {
                    self::convertValues($input->{$paramKey});
                }
            }
        }
    }
}
