<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\Parser;
use gapple\StructuredFields\Serializer;
use gapple\Tests\StructuredFields\ParsingRulesetTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Serializer::class)]
#[CoversClass(Parser::class)]
class LargeGeneratedTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'large-generated';
}
