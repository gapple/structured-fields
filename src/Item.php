<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

class Item implements TupleInterface
{
    use TupleTrait;

    /**
     * @param mixed $value
     * @param object|null $parameters
     */
    public function __construct($value, ?object $parameters = null)
    {
        $this->value = $value;

        if (is_null($parameters)) {
            $this->parameters = new Parameters();
        } else {
            $this->parameters = $parameters;
        }
    }
}
