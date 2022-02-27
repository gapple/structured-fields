<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

use stdClass;

class Parser
{
    public static function parseDictionary(string $string): stdClass
    {
        $value = [];

        $string = ltrim($string, ' ');

        while ('' !== $string) {
            $key = self::parseKey($string);

            if ('' !== $string && $string[0] === '=') {
                $string = substr($string, 1);
                $value[$key] = self::parseItemOrInnerList($string);
            } else {
                // Bare boolean true value.
                $value[$key] = [true, self::parseParameters($string)];
            }

            // OWS (optional whitespace) before comma.
            // @see https://tools.ietf.org/html/rfc7230#section-3.2.3
            $string = ltrim($string, " \t");

            if ('' === $string) {
                return (object) $value;
            }

            // OWS (optional whitespace) after comma.
            if (1 !== preg_match('/^(,[ \t]*)/', $string, $comma_matches)) {
                throw new ParseException('Expected comma');
            }

            $string = substr($string, strlen($comma_matches[1]));

            if ('' === $string) {
                throw new ParseException('Unexpected end of input');
            }
        }

        return (object) $value;
    }

    /**
     * @return array<array{0:bool|float|int|string|Token|Bytes, 1:object}>
     */
    public static function parseList(string $string): array
    {
        $value = [];

        $string = ltrim($string, ' ');

        while ('' !== $string) {
            $value[] = self::parseItemOrInnerList($string);

            // OWS (optional whitespace) before comma.
            // @see https://tools.ietf.org/html/rfc7230#section-3.2.3
            $string = ltrim($string, " \t");

            if ('' === $string) {
                return $value;
            }

            // OWS (optional whitespace) after comma.
            if (1 !== preg_match('/^(,[ \t]*)/', $string, $comma_matches)) {
                throw new ParseException('Expected comma');
            }

            $string = substr($string, strlen($comma_matches[1]));

            if ('' === $string) {
                throw new ParseException('Unexpected end of input');
            }
        }

        return $value;
    }

    /**
     * @return array{0:bool|float|int|string|Token|Bytes, 1:object}
     */
    private static function parseItemOrInnerList(string &$string): array
    {
        if ($string[0] === '(') {
            return self::parseInnerList($string);
        }

        return self::doParseItem($string);
    }

    /**
     * @param string $string
     *
     * @return array{0:bool|float|int|string|Token|Bytes, 1:object}
     */
    private static function parseInnerList(string &$string): array
    {
        $value = [];

        $string = substr($string, 1);

        while ('' !== $string) {
            $string = ltrim($string, ' ');

            if ($string[0] === ')') {
                $string = substr($string, 1);

                return [$value, self::parseParameters($string)];
            }

            $value[] = self::doParseItem($string);

            if ('' !== $string && !in_array($string[0], [' ', ')'], true)) {
                throw new ParseException('Unexpected character in inner list');
            }
        }

        throw new ParseException('Unexpected end of input');
    }

    /**
     * @return array{0:bool|float|int|string|Token|Bytes, 1:object}
     */
    public static function parseItem(string $string): array
    {
        $string = ltrim($string, ' ');
        $value = self::doParseItem($string);

        if ('' !== ltrim($string, ' ')) {
            throw new ParseException('Unexpected characters at end of input');
        }

        return $value;
    }

    /**
     * Internal implementation of parseItem that doesn't fail if input string
     * has unparsed characters after parsing.
     *
     * @param string $string
     *
     * @return array{0:bool|float|int|string|Token|Bytes, 1:object}
     */
    private static function doParseItem(string &$string): array
    {
        return [
            self::parseBareItem($string),
            self::parseParameters($string)
        ];
    }

    private static function parseBareItem(string &$string): bool|float|int|string|Bytes|Token
    {
        return match (true) {
            $string === "" => throw new ParseException('Unexpected empty input'),
            1 === preg_match('/^(-|\d)/', $string) => self::parseNumber($string),
            $string[0] == '"' =>  self::parseString($string),
            $string[0] == ':' => self::parseByteSequence($string),
            $string[0] == '?' => self::parseBoolean($string),
            1 === preg_match('/^([a-z*])/i', $string) => self::parseToken($string),
            default => throw new ParseException('Unknown item type'),
        };
    }

    private static function parseParameters(string &$string): object
    {
        $parameters = new stdClass();

        while ('' !== $string && $string[0] === ';') {
            $string = ltrim(substr($string, 1), ' ');

            $key = self::parseKey($string);
            $parameters->{$key} = true;

            if ('' !== $string && $string[0] === '=') {
                $string = substr($string, 1);
                $parameters->{$key} = self::parseBareItem($string);
            }
        }

        return $parameters;
    }

    private static function parseKey(string &$string): string
    {
        if (1 === preg_match('/^[a-z*][a-z0-9.*_-]*/', $string, $matches)) {
            $string = substr($string, strlen($matches[0]));

            return $matches[0];
        }

        throw new ParseException('Invalid character in key');
    }

    private static function parseBoolean(string &$string): bool
    {
        if (1 !== preg_match('/^\?[01]/', $string)) {
            throw new ParseException('Invalid character in boolean');
        }

        $value = $string[1] === '1';

        $string = substr($string, 2);

        return $value;
    }

    private static function parseNumber(string &$string): int|float
    {
        if (1 !== preg_match('/^(-?\d+(?:\.\d+)?)(?:[^\d.]|$)/', $string, $number_matches)) {
            throw new ParseException('Invalid number format');
        }

        $input_number = $number_matches[1];
        $string = substr($string, strlen($input_number));

        return match (true) {
            1 === preg_match('/^-?\d{1,12}\.\d{1,3}$/', $input_number) => (float) $input_number,
            1 === preg_match('/^-?\d{1,15}$/', $input_number) => (int) $input_number,
            default => throw new ParseException('Number contains too many digits'),
        };
    }

    private static function parseString(string &$string): string
    {
        // parseString is only called if first character is a double quote, so
        // don't need to validate it here.
        $string = substr($string, 1);

        $output_string = '';

        while (strlen($string)) {
            $char = $string[0];
            $string = substr($string, 1);

            if ($char == '\\') {
                if ($string == '') {
                    throw new ParseException("Invalid end of string");
                }

                $char = $string[0];
                $string = substr($string, 1);
                if ($char != '"' && $char != '\\') {
                    throw new ParseException('Invalid escaped character in string');
                }
            } elseif ($char == '"') {
                return $output_string;
            } elseif (ord($char) <= 0x1f || ord($char) >= 0x7f) {
                throw new ParseException('Invalid character in string');
            }

            $output_string .= $char;
        }

        throw new ParseException("Invalid end of string");
    }

    private static function parseToken(string &$string): Token
    {
        // Hypertext Transfer Protocol (HTTP/1.1): Message Syntax and Routing
        // 3.2.6. Field Value Components
        // @see https://tools.ietf.org/html/rfc7230#section-3.2.6
        $tchar = preg_quote("!#$%&'*+-.^_`|~");

        preg_match('/^([a-z*][a-z0-9:\/' . $tchar . ']*)/i', $string, $matches);
        $string = substr($string, strlen($matches[1]));

        // parseToken is only called by parseBareItem if the initial character
        // is valid, so a Token object is always returned.  If there is an
        // invalid character in the token, the public function that was called
        // will detect that the remainder of the input string is invalid.
        return new Token($matches[1]);
    }

    /**
     * Parse Base64-encoded data.
     */
    private static function parseByteSequence(string &$string): Bytes
    {
        if (1 === preg_match('/^:([a-z0-9+\/=]*):/i', $string, $matches)) {
            $string = substr($string, strlen($matches[0]));

            /** @var string $value */
            $value = base64_decode($matches[1], true);
            return new Bytes($value);
        }

        throw new ParseException('Invalid character in byte sequence');
    }
}
