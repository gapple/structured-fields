<?php

namespace gapple\Tests\StructuredFields;

use PHPUnit\Framework\TestCase;

abstract class RulesetTestBase extends TestCase
{
    /**
     * @return array<string, array{Rule}>
     */
    abstract protected static function rulesetDataProvider(): array;
}
