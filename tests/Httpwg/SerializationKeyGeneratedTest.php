<?php

namespace gapple\Tests\StructuredFields\Httpwg;

use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Serializer::class)]
class SerializationKeyGeneratedTest extends HttpwgTestBase
{
    protected static string $ruleset = 'serialisation-tests/key-generated';
}
