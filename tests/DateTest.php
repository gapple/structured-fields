<?php

namespace gapple\Tests\StructuredFields;

/**
 * Additional Date parsing and serializing tests.
 */
class DateTest extends RulesetTest
{
    protected function rulesetDataProvider(): array
    {
        $rules = [
            [
                "name" => "date - large int",
                "raw" => ["@1234567890123456"],
                "header_type" => "item",
                "must_fail" => true,
            ],
            [
                "name" => "date - hexadecimal",
                "raw" => ["@0x62EB2779"],
                "header_type" => "item",
                "must_fail" => true,
            ],
        ];

        $dataset = [];
        foreach ($rules as $rule) {
            $rule = (object) $rule;

            $rule->must_fail = $rule->must_fail ?? false;
            $rule->can_fail = $rule->can_fail ?? false;

            $dataset[$rule->name] = [$rule];
        }

        return $dataset;
    }
}
