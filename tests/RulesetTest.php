<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Bytes;
use gapple\StructuredFields\ParseException;
use gapple\StructuredFields\Parser;
use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use gapple\StructuredFields\Token;
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

    private function rulesetDataProvider(): array
    {
        $path = __DIR__ . '/../vendor/httpwg/structured-field-tests/' . $this->ruleset . '.json';
        if (!file_exists($path)) {
            $this->markTestSkipped('Ruleset file does not exist');
        }

        $rulesJson = file_get_contents($path);

        $rules = json_decode($rulesJson);
        if (is_null($rules) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Unable to parse ruleset JSON file.");
        }

        $dataset = [];
        foreach ($rules as $rule) {
            if (isset($rule->expected)) {
                try {
                    $rule->expected = self::{"convertExpected" . ucfirst($rule->header_type)}($rule->expected);
                } catch (\UnexpectedValueException $e) {
                    // Skip rules that cannot be parsed.
                    continue;
                }
            }

            // Set default values for optional keys.
            $rule->must_fail = $rule->must_fail ?? false;
            $rule->can_fail = $rule->can_fail ?? false;

            if (isset($dataset[$rule->name])) {
                user_error(
                    'Ruleset "' . $this->ruleset . '" contains duplicate rule name "' . $rule->name . '"',
                    E_USER_WARNING
                );
            }

            $dataset[$rule->name] = [$rule];
        }

        return $dataset;
    }

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
            if ($record->header_type == 'item') {
                $serializedValue = Serializer::serializeItem($record->expected[0], $record->expected[1]);
            } else {
                $serializedValue = Serializer::{'serialize' . ucfirst($record->header_type)}($record->expected);
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
     * Convert the expected value of an item tuple.
     *
     * @param  array  $item
     * @return array
     */
    private static function convertExpectedItem(array $item): array
    {
        return [self::convertValue($item[0]), self::convertParameters($item[1])];
    }

    /**
     * Convert the expected values of a parameters map.
     *
     * @param  array  $parameters
     * @return object
     */
    private static function convertParameters(array $parameters): object
    {
        $output = new \stdClass();

        foreach ($parameters as $value) {
            // Null byte is not supported as first character of property name.
            if (strpos($value[0], "\0") === 0) {
                throw new \UnexpectedValueException();
            }

            $output->{$value[0]} = self::convertValue($value[1]);
        }

        return $output;
    }

    /**
     * Convert the expected values of an inner list tuple.
     *
     * @param  array  $innerList
     * @return array
     */
    private static function convertInnerList(array $innerList)
    {
        $outputList = [];

        foreach ($innerList[0] as $value) {
            $outputList[] = [self::convertValue($value[0]), self::convertParameters($value[1])];
        }

        return [$outputList, self::convertParameters($innerList[1])];
    }

    /**
     * Convert the expected values of a list.
     *
     * @param  array  $list
     * @return array
     */
    private static function convertExpectedList(array $list): array
    {
        $output = [];

        foreach ($list as $value) {
            if (is_array($value[0])) {
                $output[] = self::convertInnerList($value);
            } else {
                $output[] = self::convertExpectedItem($value);
            }
        }

        return $output;
    }

    /**
     * Convert the expected values of a dictionary.
     *
     * @param  array  $dictionary
     * @return object
     */
    private static function convertExpectedDictionary(array $dictionary): object
    {
        $output = new \stdClass();

        foreach ($dictionary as $value) {
            // Null byte is not supported as first character of property name.
            if (strpos($value[0], "\0") === 0) {
                throw new \UnexpectedValueException();
            }

            if (is_array($value[1][0])) {
                $output->{$value[0]} = self::convertInnerList($value[1]);
            } else {
                $output->{$value[0]} = self::convertExpectedItem($value[1]);
            }
        }

        return $output;
    }

    /**
     * Convert any encoded special values to typed objects.
     *
     * @param mixed $data
     *   The expected bare value.
     * @return mixed
     */
    private static function convertValue($data)
    {
        if (is_object($data) && property_exists($data, '__type')) {
            switch ($data->__type) {
                case 'token':
                    return new Token($data->value);
                case 'binary':
                    return new Bytes(Base32::decodeUpper($data->value));
            }
        }

        return $data;
    }
}
