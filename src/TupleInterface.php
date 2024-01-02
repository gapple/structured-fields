<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

/**
 * Interface for objects that represent a [value, parameters] tuple.
 *
 * @see \gapple\StructuredFields\TupleTrait
 * @extends \ArrayAccess<int, mixed>
 */
interface TupleInterface extends \ArrayAccess
{
    /**
     * @return mixed
     */
    public function getValue();
    public function getParameters(): object;
}
