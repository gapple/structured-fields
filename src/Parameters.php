<?php

namespace gapple\StructuredFields;

/**
 * @implements \IteratorAggregate<string, mixed>
 */
class Parameters implements \IteratorAggregate
{
    /**
     * @var array<string, mixed>
     */
    protected $value = [];

    /**
     * @param array<mixed> $array
     * @return Parameters
     */
    public static function fromArray(array $array): Parameters
    {
        $parameters = new self();
        $parameters->value = $array;

        return $parameters;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->value[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->value[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->value[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->value[$name]);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->value);
    }
}
