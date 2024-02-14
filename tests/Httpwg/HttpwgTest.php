<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\Rule;
use gapple\Tests\StructuredFields\RulesetTest;

abstract class HttpwgTest extends RulesetTest
{
    /**
     * @var string
     */
    protected $ruleset;

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function rulesetDataProvider(): array
    {
        $path = __DIR__ . '/../../vendor/httpwg/structured-field-tests/' . $this->ruleset . '.json';
        if (!file_exists($path)) {
            throw new \RuntimeException('Ruleset file does not exist');
        }

        $rulesJson = file_get_contents($path);
        if (!$rulesJson) {
            throw new \RuntimeException("Unable to read ruleset JSON file.");
        }

        /** @var array<\stdClass>|null $rules */
        $rules = json_decode($rulesJson);
        if (is_null($rules) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Unable to parse ruleset JSON file.");
        }

        $dataset = [];
        foreach ($rules as $rawRule) {
            if (isset($rawRule->expected)) {
                try {
                    switch ($rawRule->header_type) {
                        case 'item':
                            $rawRule->expected = HttpwgRuleExpectedConverter::item($rawRule->expected);
                            break;
                        case 'list':
                            $rawRule->expected = HttpwgRuleExpectedConverter::list($rawRule->expected);
                            break;
                        case 'dictionary':
                            $rawRule->expected = HttpwgRuleExpectedConverter::dictionary($rawRule->expected);
                            break;
                        default:
                            throw new \UnexpectedValueException('Unknown header type');
                    }
                } catch (\UnexpectedValueException | \AssertionError $e) {
                    // Skip rules that cannot be parsed.
                    continue;
                }
            }
            $rule = Rule::fromClass($rawRule);

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
}
