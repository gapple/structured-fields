<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\Bytes;
use gapple\StructuredHeaders\ParseException;
use gapple\StructuredHeaders\Parser;
use gapple\StructuredHeaders\Token;
use ParagonIE\ConstantTime\Base32;
use PHPUnit\Framework\TestCase;

abstract class RulesetTest extends TestCase
{
    protected $ruleset;

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

    /**
     * @dataProvider rulesetDataProvider
     *
     * @param $record
     */
    public function testRecord($record)
    {
        // Set default values for optional keys.
        $record->must_fail = $record->must_fail ?? false;
        $record->can_fail = $record->can_fail ?? false;


        try {
            if ($record->header_type == 'item') {
                $parsedValue = Parser::parseItem($record->raw[0]);
            } elseif ($record->header_type == 'list') {
                $parsedValue = Parser::parseList(implode(',', $record->raw));
            } elseif ($record->header_type == 'dictionary') {
                $parsedValue = Parser::parseDictionary($record->raw[0]);
                $this->markTestIncomplete("Dictionary parsing is not implemented");
            }
        } catch (ParseException $e) {
            if ($record->must_fail) {
                $this->addToAssertionCount(1);
                return;
            } elseif (!$record->can_fail) {
                $this->fail('"' . $record->name . '" cannot fail parsing');
            }
        }

        if ($record->must_fail) {
            $this->fail('"' . $record->name . '" must fail parsing');
        }

        $this->assertEquals(
            $record->expected,
            $parsedValue,
            '"' . $record->name . '" was not parsed to expected value'
        );
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
