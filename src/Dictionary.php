<?php

namespace gapple\StructuredFields;

class Dictionary implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $value = [];

    public static function fromArray(array $array): Dictionary
    {
        $dictionary = new static();

        foreach ($array as $key => $value) {
            if (!$value instanceof TupleInterface) {
                if (is_array($value)) {
                    $value = InnerList::fromArray($value);
                } else {
                    $value = new Item($value);
                }
            }
            $dictionary->{$key} = $value;
        }

        return $dictionary;
    }

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
