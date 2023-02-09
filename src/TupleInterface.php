<?php

namespace gapple\StructuredFields;

/**
 * Interface for objects that represent a [value, parameters] tuple.
 *
 * @see \gapple\StructuredFields\TupleTrait
 */
interface TupleInterface extends \ArrayAccess
{
    public function getValue();
    public function getParameters(): object;
}
