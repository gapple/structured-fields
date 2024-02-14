<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\Bytes;
use gapple\StructuredFields\Date;
use gapple\StructuredFields\Dictionary;
use gapple\StructuredFields\DisplayString;
use gapple\StructuredFields\InnerList;
use gapple\StructuredFields\Item;
use gapple\StructuredFields\OuterList;
use gapple\StructuredFields\Parameters;
use gapple\StructuredFields\Token;
use ParagonIE\ConstantTime\Base32;

/**
 * @phpstan-type ExpectedItem array{ExpectedBareValue, ExpectedParameters}
 * @phpstan-type ExpectedDictionary array<array{string, ExpectedTuple}>
 * @phpstan-type ExpectedOuterList array<ExpectedTuple>
 * @phpstan-type ExpectedInnerList array{array<ExpectedItem>, ExpectedParameters}
 * @phpstan-type ExpectedTuple ExpectedItem|ExpectedInnerList
 * @phpstan-type ExpectedBareValue bool|int|float|string|ExpectedTypedValue
 * @phpstan-type ExpectedTypedValue object{__type: 'binary'|'date'|'displaystring'|'token', value: int|string}
 * @phpstan-type ExpectedParameters array<array{string, ExpectedBareValue}>
 */
class HttpwgRuleExpectedConverter
{
    /**
     * Convert the expected value of an item tuple.
     *
     * @param  ExpectedItem $item
     * @return Item
     */
    public static function item(array $item): Item
    {
        return new Item(self::value($item[0]), self::parameters($item[1]));
    }

    /**
     * Convert the expected values of a dictionary.
     *
     * @param  ExpectedDictionary $dictionary
     * @return Dictionary
     */
    public static function dictionary(array $dictionary): Dictionary
    {
        $output = new Dictionary();

        foreach ($dictionary as $value) {
            // Null byte is not supported as first character of property name.
            if (strpos($value[0], "\0") === 0) {
                throw new \UnexpectedValueException();
            }

            if (is_array($value[1][0])) {
                $output->{$value[0]} = self::innerList($value[1]);
            } else {
                $output->{$value[0]} = self::item($value[1]);
            }
        }

        return $output;
    }

    /**
     * Convert the expected values of a list.
     *
     * @param  ExpectedOuterList $list
     * @return OuterList
     */
    public static function list(array $list): OuterList
    {
        $output = new OuterList();

        foreach ($list as $value) {
            if (is_array($value[0])) {
                $output[] = self::innerList($value);
            } else {
                $output[] = self::item($value);
            }
        }

        return $output;
    }

    /**
     * Convert the expected values of a parameters map.
     *
     * @param  ExpectedParameters $parameters
     * @return Parameters
     */
    private static function parameters(array $parameters): Parameters
    {
        $output = new Parameters();

        foreach ($parameters as $value) {
            // Null byte is not supported as first character of property name.
            if (strpos($value[0], "\0") === 0) {
                throw new \UnexpectedValueException();
            }

            $output->{$value[0]} = self::value($value[1]);
        }

        return $output;
    }

    /**
     * Convert the expected values of an inner list tuple.
     *
     * @param  ExpectedInnerList $innerList
     * @return InnerList
     */
    private static function innerList(array $innerList): InnerList
    {
        $outputList = [];

        foreach ($innerList[0] as $value) {
            $outputList[] = new Item(self::value($value[0]), self::parameters($value[1]));
        }

        return new InnerList($outputList, self::parameters($innerList[1]));
    }

    /**
     * Convert any encoded special values to typed objects.
     *
     * @param ExpectedBareValue $data
     *   The expected bare value.
     * @return bool|int|float|string|Bytes|Date|DisplayString|Token
     */
    private static function value($data)
    {
        if (!is_object($data)) {
            return $data;
        }

        if (property_exists($data, '__type')) {
            switch ($data->__type) {
                case 'binary':
                    assert(is_string($data->value));
                    return new Bytes(Base32::decodeUpper($data->value));
                case 'date':
                    assert(is_int($data->value));
                    return new Date($data->value);
                case 'displaystring':
                    assert(is_string($data->value));
                    return new DisplayString($data->value);
                case 'token':
                    assert(is_string($data->value));
                    return new Token($data->value);
            }
        }

        throw new \UnexpectedValueException("Unknown value type");
    }
}
