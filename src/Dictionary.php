<?php

namespace gapple\StructuredFields;

class Dictionary implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $value = [];

    public function __get($name)
    {
        return $this->value[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->value[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->value[$name]);
    }

    public function __unset($name)
    {
        unset($this->value[$name]);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->value);
    }
}
