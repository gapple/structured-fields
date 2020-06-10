<?php

namespace gapple\Tests\StructuredHeaders;

use gapple\StructuredHeaders\Serializer;
use PHPUnit\Framework\TestCase;

class SerializeInnerListTest extends TestCase
{

    /**
     * Items within an inner list can have parameters.
     */
    public function testInnerListItemWithParameters()
    {
        $item = [
            [ // Inner List
                [[1, (object) ['a' => 'b']], [2, new \stdClass()]],
                new \stdClass(),
            ],
            [ // Inner List
                [[42, new \stdClass()], [43, (object) ['c' => 'd']]],
                new \stdClass()
            ],
        ];

        $serialized = Serializer::serializeList($item);

        $this->assertEquals('(1;a="b" 2), (42 43;c="d")', $serialized);
    }
}
