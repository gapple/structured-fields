<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\Parser;
use gapple\StructuredFields\Serializer;
use gapple\StructuredFields\Token;
use gapple\Tests\StructuredFields\ParsingRulesetTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Serializer::class)]
#[CoversClass(Parser::class)]
#[CoversClass(Token::class)]
class TokenTest extends HttpwgTestBase
{
    use ParsingRulesetTrait;

    protected static string $ruleset = 'token';
}
