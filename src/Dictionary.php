<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

/**
 * @implements \IteratorAggregate<string, TupleInterface|array{mixed, object}>
 */
class Dictionary implements \IteratorAggregate
{
    /**
     * @var array<string, TupleInterface|array{mixed, object}>
     */
    protected $value = [];

    /**
     * @param array<mixed> $array
     * @return Dictionary
     */
    public static function fromArray(array $array): Dictionary
    {
        $dictionary = new self();

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

    /**
     * @param string $name
     * @return TupleInterface|array{mixed, object}|null
     */
    public function __get(string $name)
    {
        return $this->value[$name] ?? null;
    }

    /**
     * @param string $name
     * @param TupleInterface|array{mixed, object} $value
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
