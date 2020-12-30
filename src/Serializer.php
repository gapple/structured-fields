<?php

namespace gapple\StructuredFields;

class Serializer
{
    /**
     * Serialize and item with optional parameters.
     *
     * @param $value
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

            $bareValue = $value[0];
            $parameters = $value[1];
        } else {
            $bareValue = $value;
        }

        $output = self::serializeBareItem($bareValue);

        if (!empty($parameters)) {
            $output .= self::serializeParameters($parameters);
        }

        return $output;
    }

    public static function serializeList($value): string
    {
        if ($value instanceof OuterList) {
            $value = iterator_to_array($value->getIterator());
        }

        $returnValue = array_map(function ($item) {
            if (is_array($item[0])) {
                return self::serializeInnerList($item[0], $item[1]);
            } else {
                return self::serializeItem($item[0], $item[1]);
            }
        }, $value);

        return implode(', ', $returnValue);
    }

    public static function serializeDictionary(object $value): string
    {
        $members = get_object_vars($value);
        $keys = array_keys($members);

        $returnValue = array_map(function ($item, $key) {
            $returnValue = self::serializeKey($key);

            if ($item[0] === true) {
                $returnValue .= self::serializeParameters($item[1]);
            } elseif (is_array($item[0])) {
                $returnValue .= '=' . self::serializeInnerList($item[0], $item[1]);
            } else {
                $returnValue .= '=' . self::serializeItem($item[0], $item[1]);
            }
            return $returnValue;
        }, $members, $keys);

        return implode(', ', $returnValue);
    }

    private static function serializeInnerList(array $value, ?object $parameters = null): string
    {
        $returnValue = '(';

        while ($item = array_shift($value)) {
            $returnValue .= self::serializeItem($item[0], $item[1]);

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
            return self::serializeString($value);
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
        return $value;
    }

    private static function serializeDecimal(float $value): string
    {
        if (abs(floor($value)) > 999999999999) {
            throw new SerializeException("Integer portion of decimals is limited to 12 digits");
        }

        // Casting to a string loses a digit on long numbers, but is preserved
        // by json_encode (e.g. 111111111111.111).
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

        foreach (get_object_vars($value) as $key => $value) {
            $returnValue .= ';' . self::serializeKey($key);

            if ($value !== true) {
                $returnValue .= '=' . self::serializeBareItem($value);
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
