<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\Serializer;
use gapple\StructuredFields\Token;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Serializer::class)]
#[CoversClass(Token::class)]
class SerializationTokenGeneratedTest extends HttpwgTestBase
{
    protected static string $ruleset = 'serialisation-tests/token-generated';
}
