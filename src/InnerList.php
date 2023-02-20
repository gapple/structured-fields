<?php

namespace gapple\StructuredFields;

class InnerList implements TupleInterface
{
    use TupleTrait;

    /**
     * @param array<TupleInterface|array{mixed, object}> $value
     * @param object|null $parameters
     */
    public function __construct(array $value, ?object $parameters = null)
    {
        array_walk($value, [$this, 'validateItemType']);

        $this->value = $value;

        if (is_null($parameters)) {
            $this->parameters = new Parameters();
        } else {
            $this->parameters = $parameters;
        }
    }

    /**
     * Create an InnerList from an array of bare values.
     *
     * @param array<mixed> $array
     *   An array of bare items or TupleInterface objects.
     * @return InnerList
     */
    public static function fromArray(array $array): InnerList
    {
        array_walk($array, function (&$item) {
            if (!$item instanceof TupleInterface) {
                $item = new Item($item);
            }
            self::validateItemType($item);
        });

        /** @var TupleInterface[] $array */
        return new self($array);
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
            if ($value instanceof InnerList) {
                throw new \InvalidArgumentException('InnerList objects cannot be nested');
            }
        } elseif (is_array($value)) {
            if (count($value) != 2) { // @phpstan-ignore-line
                throw new \InvalidArgumentException();
            }
        } else {
            throw new \InvalidArgumentException();
        }
    }
}
