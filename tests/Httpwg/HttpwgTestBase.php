<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\Tests\StructuredFields\Rule;
use gapple\Tests\StructuredFields\RulesetTestBase;
use gapple\Tests\StructuredFields\SerializingRulesetTrait;

abstract class HttpwgTestBase extends RulesetTestBase
{
    use SerializingRulesetTrait;

    protected static string $ruleset;

    /**
     * @return array<string, array{Rule}>
     */
    protected static function rulesetDataProvider(): array
    {
        $path = __DIR__ . '/../../vendor/httpwg/structured-field-tests/' . static::$ruleset . '.json';
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
                    $rawRule->expected = match ($rawRule->header_type) {
                        'item' => HttpwgRuleExpectedConverter::item($rawRule->expected),
                        'list' => HttpwgRuleExpectedConverter::list($rawRule->expected),
                        'dictionary' => HttpwgRuleExpectedConverter::dictionary($rawRule->expected),
                        default => throw new \UnexpectedValueException('Unknown header type'),
                    };
                } catch (\UnexpectedValueException | \AssertionError $e) {
                    // Skip rules that cannot be parsed.
                    continue;
                }
            }
            $rule = Rule::fromClass($rawRule); // @phpstan-ignore argument.type

            if (isset($dataset[$rule->name])) {
                user_error(
                    'Ruleset "' . static::$ruleset . '" contains duplicate rule name "' . $rule->name . '"',
                    E_USER_WARNING
                );
            }

            $dataset[$rule->name] = [$rule];
        }

        return $dataset;
    }
}
