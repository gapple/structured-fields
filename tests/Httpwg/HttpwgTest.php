<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\Bytes;
use gapple\StructuredFields\Date;
use gapple\StructuredFields\InnerList;
use gapple\StructuredFields\Item;
use gapple\StructuredFields\OuterList;
use gapple\StructuredFields\Token;
use gapple\Tests\StructuredFields\RulesetTest;
use ParagonIE\ConstantTime\Base32;

abstract class HttpwgTest extends RulesetTest
{
    protected $ruleset;

    protected function rulesetDataProvider(): array
    {
        $path = __DIR__ . '/../../vendor/httpwg/structured-field-tests/' . $this->ruleset . '.json';
        if (!file_exists($path)) {
            throw new \RuntimeException('Ruleset file does not exist');
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

    /**
     * Convert the expected value of an item tuple.
     *
     * @param  array  $item
     * @return \gapple\StructuredFields\Item
     */
    private static function convertExpectedItem(array $item): Item
    {
        return new Item(self::convertValue($item[0]), self::convertParameters($item[1]));
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
     * @return InnerList
     */
    private static function convertInnerList(array $innerList)
    {
        $outputList = [];

        foreach ($innerList[0] as $value) {
            $outputList[] = new Item(self::convertValue($value[0]), self::convertParameters($value[1]));
        }

        return new InnerList($outputList, self::convertParameters($innerList[1]));
    }

    /**
     * Convert the expected values of a list.
     *
     * @param  array  $list
     * @return OuterList
     */
    private static function convertExpectedList(array $list): OuterList
    {
        $output = new OuterList();

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
                case 'date':
                    return new Date($data->value);
            }
        }

        return $data;
    }
}
