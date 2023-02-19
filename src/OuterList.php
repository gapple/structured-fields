<?php

namespace gapple\StructuredFields;

/**
 * @implements \IteratorAggregate<int, TupleInterface|array{mixed, object}>
 * @implements \ArrayAccess<int, TupleInterface|array{mixed, object}>
 */
class OuterList implements \IteratorAggregate, \ArrayAccess
{
    /**
     * The array of values.
     *
     * @var array<TupleInterface|array{mixed, object}>
     */
    public $value;

    /**
     * @param array<TupleInterface|array{mixed, object}> $value
     */
    public function __construct(array $value = [])
    {
        array_walk($value, [$this, 'validateItemType']);

        $this->value = $value;
    }

    /**
     * Create an OuterList from an array of bare values.
     *
     * @param array<mixed> $array
     * @return OuterList
     */
    public static function fromArray(array $array): OuterList
    {
        $list = new self();
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

    /**
     * @param TupleInterface|array{mixed, object} $value
     * @return void
     */
    private static function validateItemType($value): void
    {
        if (is_object($value)) {
            if (!($value instanceof TupleInterface)) {
                throw new \InvalidArgumentException(
                    'Objects as list values must implement ' . TupleInterface::class
                );
            }
        } elseif (is_array($value)) {
            if (count($value) != 2) { // @phpstan-ignore-line
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

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->value[$offset]);
    }

    /**
     * @param int $offset
     * @return TupleInterface|array{mixed, object}|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->value[$offset] ?? null;
    }

    /**
     * @param int|null $offset
     * @param TupleInterface|array{mixed, object} $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        self::validateItemType($value);

        if (is_null($offset)) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
    }

    /**
     * @param int $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->value[$offset]);
    }
}
