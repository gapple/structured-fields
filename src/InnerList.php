<?php

namespace gapple\StructuredFields;

class InnerList implements TupleInterface
{
    use TupleTrait;

    public function __construct(array $value, ?object $parameters = null)
    {
        array_walk($value, [$this, 'validateItemType']);

        $this->value = $value;

        if (is_null($parameters)) {
            $this->parameters = new \stdClass();
        } else {
            $this->parameters = $parameters;
        }
    }

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
            if (count($value) != 2) {
                throw new \InvalidArgumentException();
            }
        } else {
            throw new \InvalidArgumentException();
        }
    }
}
