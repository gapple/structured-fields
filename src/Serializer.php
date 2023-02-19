<?php

namespace gapple\StructuredFields;

class Serializer
{
    /**
     * Serialize an item with optional parameters.
     *
     * @param mixed $value
     *   A bare value, or an Item object.
     * @param object|null $parameters
     *   An optional object containing parameter values if a bare value is provided.
     *
     * @return string
     *   The serialized value.
     */
    public static function serializeItem($value, ?object $parameters = null): string
    {
        if ($value instanceof Item) {
            if (!is_null($parameters)) {
                throw new \InvalidArgumentException(
                    'Parameters argument is not allowed when serializing an Item object'
                );
            }

            $bareValue = $value->getValue();
            $parameters = $value->getParameters();
        } else {
            $bareValue = $value;
        }

        $output = self::serializeBareItem($bareValue);

        if (!empty($parameters)) {
            $output .= self::serializeParameters($parameters);
        }

        return $output;
    }

    /**
     * @param iterable<TupleInterface|array{mixed, object}> $value
     * @return string
     */
    public static function serializeList(iterable $value): string
    {
        if ($value instanceof \Traversable) {
            if ($value instanceof \IteratorAggregate) {
                $value = $value->getIterator();
            }
            $value = iterator_to_array($value);
        }

        $returnValue = array_map(function ($item) {
            if ($item instanceof TupleInterface) {
                $itemValue = $item->getValue();
                $itemParameters = $item->getParameters();
            } else {
                $itemValue = $item[0];
                $itemParameters = $item[1];
            }

            if (is_array($itemValue)) {
                return self::serializeInnerList($itemValue, $itemParameters);
            } else {
                return self::serializeItem($itemValue, $itemParameters);
            }
        }, $value);

        return implode(', ', $returnValue);
    }

    /**
     * Serialize an object as a dictionary.
     *
     * Either a Traversable object can be provided, or the public properties of the object will be extracted.
     *
     * @param Dictionary|object $value
     * @return string
     */
    public static function serializeDictionary(object $value): string
    {
        $returnValue = '';

        if (!$value instanceof \Traversable) {
            $value = get_object_vars($value);
        }

        foreach ($value as $key => $item) {
            if (!empty($returnValue)) {
                $returnValue .= ', ';
            }

            $returnValue .= self::serializeKey($key);

            if ($item instanceof TupleInterface) {
                $itemValue = $item->getValue();
                $itemParameters = $item->getParameters();
            } else {
                $itemValue = $item[0];
                $itemParameters = $item[1];
            }

            if ($itemValue === true) {
                $returnValue .= self::serializeParameters($itemParameters);
            } elseif (is_array($itemValue)) {
                $returnValue .= '=' . self::serializeInnerList($itemValue, $itemParameters);
            } else {
                $returnValue .= '=' . self::serializeItem($itemValue, $itemParameters);
            }
        }

        return $returnValue;
    }

    /**
     * @param array<TupleInterface|array{mixed, object}> $value
     * @param object|null $parameters
     */
    private static function serializeInnerList(array $value, ?object $parameters = null): string
    {
        $returnValue = '(';

        while ($item = array_shift($value)) {
            if ($item instanceof TupleInterface) {
                $returnValue .= self::serializeItem($item);
            } else {
                $returnValue .= self::serializeItem($item[0], $item[1]);
            }

            if (!empty($value)) {
                $returnValue .= ' ';
            }
        }

        $returnValue .= ')';

        if (!empty($parameters)) {
            $returnValue .= self::serializeParameters($parameters);
        }

        return $returnValue;
    }

    /**
     * @param mixed $value
     */
    private static function serializeBareItem($value): string
    {
        if (is_int($value)) {
            return self::serializeInteger($value);
        } elseif (is_float($value)) {
            return self::serializeDecimal($value);
        } elseif (is_bool($value)) {
            return self::serializeBoolean($value);
        } elseif ($value instanceof Token) {
            return self::serializeToken($value);
        } elseif ($value instanceof Bytes) {
            return self::serializeByteSequence($value);
        } elseif ($value instanceof Date) {
            return self::serializeDate($value);
        } elseif (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return self::serializeString((string) $value);
        }

        throw new SerializeException("Unrecognized type");
    }

    private static function serializeBoolean(bool $value): string
    {
        return '?' . ($value ? '1' : '0');
    }

    private static function serializeInteger(int $value): string
    {
        if ($value > 999999999999999 || $value < -999999999999999) {
            throw new SerializeException("Integers are limited to 15 digits");
        }
        return (string) $value;
    }

    private static function serializeDecimal(float $value): string
    {
        if (abs(floor($value)) > 999999999999) {
            throw new SerializeException("Integer portion of decimals is limited to 12 digits");
        }

        // Casting to a string loses a digit on long numbers, but is preserved
        // by json_encode (e.g. 111111111111.111).
        /** @var string $result */
        $result = json_encode(round($value, 3, PHP_ROUND_HALF_EVEN));

        if (strpos($result, '.') === false) {
            $result .= '.0';
        }

        return $result;
    }

    private static function serializeString(string $value): string
    {
        if (preg_match('/[^\x20-\x7E]/i', $value)) {
            throw new SerializeException("Invalid characters in string");
        }

        return '"' . preg_replace('/(["\\\])/', '\\\$1', $value) . '"';
    }

    private static function serializeToken(Token $value): string
    {
        // Hypertext Transfer Protocol (HTTP/1.1): Message Syntax and Routing
        // 3.2.6. Field Value Components
        // @see https://tools.ietf.org/html/rfc7230#section-3.2.6
        $tchar = preg_quote("!#$%&'*+-.^_`|~");

        if (!preg_match('/^((?:\*|[a-z])[a-z0-9:\/' . $tchar . ']*)$/i', $value)) {
            throw new SerializeException('Invalid characters in token');
        }

        return $value;
    }

    private static function serializeByteSequence(Bytes $value): string
    {
        return ':' . base64_encode($value) . ':';
    }

    private static function serializeParameters(object $value): string
    {
        $returnValue = '';

        if (!$value instanceof Parameters) {
            $value = get_object_vars($value);
        }

        foreach ($value as $key => $item) {
            $returnValue .= ';' . self::serializeKey($key);

            if ($item !== true) {
                $returnValue .= '=' . self::serializeBareItem($item);
            }
        }

        return $returnValue;
    }

    private static function serializeDate(Date $value): string
    {
        return '@' . self::serializeInteger($value->toInt());
    }

    private static function serializeKey(string $value): string
    {
        if (!preg_match('/^[a-z*][a-z0-9.*_-]*$/', $value)) {
            throw new SerializeException("Invalid characters in key");
        }

        return $value;
    }
}
