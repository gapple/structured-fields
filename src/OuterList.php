<?php

namespace gapple\StructuredFields;

class OuterList implements \IteratorAggregate, \ArrayAccess
{
    /**
     * The array of values.
     *
     * @var array
     */
    public $value;

    public function __construct($value = [])
    {
        array_walk($value, [$this, 'validateItemType']);

        $this->value = $value;
    }

    /**
     * Create an OuterList from an array of bare values.
     *
     * @param array $array
     * @return OuterList
     */
    public static function fromArray(array $array): OuterList
    {
        $list = new static();
        foreach ($array as $value) {
            if (!$value instanceof TupleInterface) {
                if (is_array($value)) {
                    $value = InnerList::fromArray($value);
                } else {
                    $value = new Item($value);
                }
            }
            $list[] = $value;
        }

        return $list;
    }

    private static function validateItemType($value): void
    {
        if (is_object($value)) {
            if (!($value instanceof TupleInterface)) {
                throw new \InvalidArgumentException(
                    'Objects as list values must implement ' . TupleInterface::class
                );
            }
        } elseif (is_array($value)) {
            if (count($value) != 2) {
                throw new \InvalidArgumentException();
            }
        } else {
             throw new \InvalidArgumentException();
        }
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->value);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->value[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->value[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        static::validateItemType($value);

        if (is_null($offset)) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->value[$offset]);
    }
}
