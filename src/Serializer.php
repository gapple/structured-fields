<?php

namespace gapple\StructuredHeaders;

class Serializer
{

    public static function serializeItem($value, $parameters = null): string
    {
        $output = self::serializeBareItem($value);

        if (!empty($parameters)) {
            $output .= self::serializeParameters($parameters);
        }

        return $output;
    }

    public static function serializeList($value): string
    {
        return '';
    }

    public static function serializeDictionary($value): string
    {
        return '';
    }

    private static function serializeBareItem($value): string
    {
        if (is_int($value)) {
            return self::serializeInteger($value);
        } elseif (is_float($value)) {
            return self::serializeDecimal($value);
        } elseif (is_string($value)) {
            return self::serializeString($value);
        } elseif ($value instanceof Token) {
            return self::serializeToken($value);
        } elseif (is_bool($value)) {
            return self::serializeBoolean($value);
        } elseif ($value instanceof Bytes) {
            return self::serializeByteSequence($value);
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
        if (floor($value) > 999999999999 || floor($value) < -999999999999) {
            throw new SerializeException("Integer portion of decimals is limited to 12 digits");
        }

        $returnValue = (string) round($value, 3, PHP_ROUND_HALF_EVEN);

        if (stripos($returnValue, '.') === false) {
            $returnValue .= '.0';
        }

        return $returnValue;
    }

    private static function serializeString(string $value): string
    {
        if (preg_match('/[^\x00-\x7F]/i', $value)) {
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

        if (!preg_match('/^((?:\*|[a-z])[a-z0-9:\/' . $tchar . ']*)/i', $value)) {
            throw new SerializeException('Invalid characters in token');
        }

        return $value;
    }

    private static function serializeByteSequence(Bytes $value): string
    {
        return ':' . base64_encode($value) . ':';
    }

    private static function serializeParameters($value): string
    {
        return '';
    }
}
