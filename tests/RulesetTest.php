<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\ParseException;
use gapple\StructuredHeaders\Parser;
use gapple\StructuredHeaders\Token;
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

        foreach ($record->raw as $value) {
            try {
                if ($record->header_type == 'item') {
                    $parsedValue = Parser::parseItem($value);

                    if (isset($record->expected) && $record->expected[0] instanceof \stdClass) {
                        if ($record->expected[0]->__type == 'token') {
                            $record->expected[0] = new Token($record->expected[0]->value);
                        }
                    }
                } elseif ($record->header_type == 'list') {
                    $parsedValue = Parser::parseList($value);
                    $this->markTestIncomplete("List parsing is not implemented");
                } elseif ($record->header_type == 'dictionary') {
                    $parsedValue = Parser::parseDictionary($value);
                    $this->markTestIncomplete("Dictionary parsing is not implemented");
                }
            } catch (ParseException $e) {
                if ($record->must_fail) {
                    $this->addToAssertionCount(1);
                    continue;
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
    }
}
