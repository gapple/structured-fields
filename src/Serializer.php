<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

use Stringable;

class Serializer
{
    public static function serializeItem(mixed $value, object|null $parameters = null): string
    {
        $output = self::serializeBareItem($value);

        if (null !== $parameters) {
            $output .= self::serializeParameters($parameters);
        }

        return $output;
    }

    /**
     * @param array{0:mixed, 1:object} $value
     */
    public static function serializeList(array $value): string
    {
        return implode(', ', array_map(
            fn (array $item): string => is_array($item[0]) ?
                self::serializeInnerList($item[0], $item[1]) :
                self::serializeItem($item[0], $item[1]),
            $value
        ));
    }

    public static function serializeDictionary(object $value): string
    {
        $members = get_object_vars($value);

        $mapper = fn (array $item, string $key): string => match (true) {
            $item[0] === true => self::serializeKey($key) . self::serializeParameters($item[1]),
            is_array($item[0]) => self::serializeKey($key) . '=' . self::serializeInnerList($item[0], $item[1]),
            default => self::serializeKey($key) . '=' . self::serializeItem($item[0], $item[1]),
        };

        return implode(', ', array_map($mapper, $members, array_keys($members)));
    }

    /**
     * @param array{0:mixed, 1:object} $value
     */
    private static function serializeInnerList(array $value, object|null $parameters = null): string
    {
        $returnValue = '(';

        while ($item = array_shift($value)) {
            $returnValue .= self::serializeItem($item[0], $item[1]);

            if ([] !== $value) {
                $returnValue .= ' ';
            }
        }

        $returnValue .= ')';

        if (null !== $parameters) {
            $returnValue .= self::serializeParameters($parameters);
        }

        return $returnValue;
    }

    private static function serializeBareItem(mixed $value): string
    {
        return match (true) {
            $value instanceof Token => self::serializeToken($value),
            $value instanceof Bytes => self::serializeByteSequence($value),
            is_int($value) => self::serializeInteger($value),
            is_float($value) => self::serializeDecimal($value),
            is_bool($value) => self::serializeBoolean($value),
            $value instanceof Stringable, is_string($value) => self::serializeString($value),
            default => throw new SerializeException("Unrecognized type"),
        };
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

        if (!str_contains($result, '.')) {
            $result .= '.0';
        }

        return $result;
    }

    private static function serializeString(Stringable|string $value): string
    {
        $value = (string) $value;

        if (1 === preg_match('/[^\x20-\x7E]/i', $value)) {
            throw new SerializeException("Invalid characters in string");
        }

        return '"' . preg_replace('/(["\\\])/', '\\\$1', $value) . '"';
    }

    private static function serializeToken(Token $value): string
    {
        $value = (string) $value;

        // Hypertext Transfer Protocol (HTTP/1.1): Message Syntax and Routing
        // 3.2.6. Field Value Components
        // @see https://tools.ietf.org/html/rfc7230#section-3.2.6
        $tchar = preg_quote("!#$%&'*+-.^_`|~");
        if (1 !== preg_match('/^((?:\*|[a-z])[a-z0-9:\/' . $tchar . ']*)$/i', $value)) {
            throw new SerializeException('Invalid characters in token');
        }

        return $value;
    }

    private static function serializeByteSequence(Bytes $value): string
    {
        return ':' . base64_encode((string) $value) . ':';
    }

    private static function serializeParameters(object $value): string
    {
        $returnValue = '';

        foreach (get_object_vars($value) as $key => $val) {
            $returnValue .= ';' . self::serializeKey($key);

            if ($val !== true) {
                $returnValue .= '=' . self::serializeBareItem($val);
            }
        }

        return $returnValue;
    }

    private static function serializeKey(string $value): string
    {
        if (1 !== preg_match('/^[a-z*][a-z0-9.*_-]*$/', $value)) {
            throw new SerializeException("Invalid characters in key");
        }

        return $value;
    }
}
