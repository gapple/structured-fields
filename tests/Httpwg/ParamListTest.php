<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\OuterList;
use gapple\StructuredFields\Parameters;
use gapple\StructuredFields\Parser;
use gapple\StructuredFields\Serializer;
use gapple\Tests\StructuredFields\ParsingRulesetTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Serializer::class)]
#[CoversClass(Parser::class)]
#[CoversClass(OuterList::class)]
#[CoversClass(Parameters::class)]
class ParamListTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'param-list';
}
