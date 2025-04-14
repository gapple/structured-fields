<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\Dictionary;
use gapple\StructuredFields\Parameters;
use gapple\StructuredFields\Parser;
use gapple\StructuredFields\Serializer;
use gapple\Tests\StructuredFields\ParsingRulesetTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Serializer::class)]
#[CoversClass(Parser::class)]
#[CoversClass(Dictionary::class)]
#[CoversClass(Parameters::class)]
class ParamDictTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'param-dict';
}
