<?php

namespace gapple\Tests\StructuredFields;

use PHPUnit\Framework\TestCase;

abstract class RulesetTestBase extends TestCase
{
    /**
     * An array of rules which should skip the parsing test.
     *
     * The element key should be the name of the rule, and the value should be
     * the message to provide for skipping the rule.
     *
     * @var array<string, string>
     */
    protected array $skipParsingRules = [];

    /**
     * An array of rules which should skip the serializing test.
     *
     * The element key should be the name of the rule, and the value should be
     * the message to provide for skipping the rule.
     *
     * @var array<string, string>
     */
    protected array $skipSerializingRules = [];

    /**
     * @return array<string, array{Rule}>
     */
    abstract protected static function rulesetDataProvider(): array;
}
