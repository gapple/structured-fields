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

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getParameters(): object
    {
        return $this->parameters;
    }

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return $offset === 0 || $offset === 1;
    }

    /**
     * @param int $offset
     * @return mixed|object|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($offset == 0) {
            return $this->value;
        } elseif ($offset == 1) {
            return $this->parameters;
        }
        return null;
    }

    /**
     * @param int $offset
     * @param mixed|object $value
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset == 0) {
            $this->value = $value;
        } elseif ($offset == 1) {
            $this->parameters = $value;
        }
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        if ($offset == 0) {
            $this->value = null;
        } elseif ($offset == 1) {
            $this->parameters = new Parameters();
        }
    }
}
