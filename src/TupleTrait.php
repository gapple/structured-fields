<?php

namespace gapple\StructuredFields;

trait TupleTrait
{
    /**
     * The tuple's value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The tuple's parameters
     *
     * @var object
     */
    protected $parameters;

    public function offsetExists($offset): bool
    {
        return $offset == 0 || $offset == 1;
    }

    public function offsetGet($offset)
    {
        if ($offset == 0) {
            return $this->value;
        } elseif ($offset == 1) {
            return $this->parameters;
        }
        return null;
    }

    public function offsetSet($offset, $value)
    {
        if ($offset == 0) {
            $this->value = $value;
        } elseif ($offset == 1) {
            $this->parameters = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if ($offset == 0) {
            $this->value = null;
        } elseif ($offset == 1) {
            $this->parameters = new \stdClass();
        }
    }
}
