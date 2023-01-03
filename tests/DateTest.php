<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Date;

/**
 * Test Date parsing and serializing.
 *
 * @todo Replace with httpwg test cases when available
 *       https://github.com/httpwg/structured-field-tests/pull/83
 */
class DateTest extends RulesetTest
{
    protected function rulesetDataProvider(): array
    {
        $rules = [
            [
                "name" => "date - 1970-01-01 00:00:00",
                "raw" => ["@0"],
                "header_type" => "item",
                "expected" => [new Date(0), new \stdClass()],
            ],
            [
                "name" => "date - 2022-08-04 01:57:13",
                "raw" => ["@1659578233"],
                "header_type" => "item",
                "expected" => [new Date(1659578233), new \stdClass()],
            ],
            [
                "name" => "date - 1917-05-30 22:02:47",
                "raw" => ["@-1659578233"],
                "header_type" => "item",
                "expected" => [new Date(-1659578233), new \stdClass()],
            ],
            [
                "name" => "date - 2^31",
                "raw" => ["@2147483648"],
                "header_type" => "item",
                "expected" => [new Date(2147483648), new \stdClass()],
            ],
            [
                "name" => "date - 2^32",
                "raw" => ["@4294967296"],
                "header_type" => "item",
                "expected" => [new Date(4294967296), new \stdClass()],
            ],
            [
                "name" => "date - decimal",
                "raw" => ["@1659578233.12"],
                "header_type" => "item",
                "must_fail" => true,
            ],
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
