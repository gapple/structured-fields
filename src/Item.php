<?php

namespace gapple\StructuredFields;

class Item implements TupleInterface
{
    use TupleTrait;

    public function __construct($value, ?object $parameters = null)
    {
        $this->value = $value;

        if (is_null($parameters)) {
            $this->parameters = new \stdClass();
        } else {
            $this->parameters = $parameters;
        }
    }
}
