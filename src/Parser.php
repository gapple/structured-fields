<?php

namespace gapple\StructuredHeaders;

class Parser
{

    public static function parseDictionary(string $string): array
    {
        return [];
    }

    public static function parseList(string $string): array
    {
        $value = [];

        $string = ltrim($string, ' ');

        while (!empty($string)) {
            if ($string[0] === '(') {
                $value[] = self::parseInnerList($string);
            } else {
                $value[] = self::doParseItem($string);
            }

            $string = ltrim($string, ' ');

            if (empty($string)) {
                return $value;
            }

            if (!preg_match('/^( *, *)/', $string, $comma_matches)) {
                throw new ParseException();
            }

            $string = substr($string, strlen($comma_matches[1]));

            if (empty($string)) {
                throw new ParseException();
            }
        }

        return $value;
    }

    private static function parseInnerList(string &$string): array
    {
        $value = [];

        $string = substr($string, 1);

        while (!empty($string)) {
            $string = ltrim($string, ' ');

            if ($string[0] === ')') {
                $string = substr($string, 1);
                return [
                    $value,
                    self::parseParameters($string),
                ];
            }

            $value[] = self::doParseItem($string);

            if (!empty($string) && !in_array($string[0], [' ', ')'])) {
                throw new ParseException();
            }
        }

        throw new ParseException();
    }

    /**
     * @param string $string
     *
     * @return array
     *  A [value, parameters] tuple.
     */
    public static function parseItem(string $string): array
    {
        $string = ltrim($string, ' ');

        $value = self::doParseItem($string);

        if (empty(ltrim($string, ' '))) {
            return $value;
        }

        throw new ParseException();
    }

    /**
     * Internal implementation of parseItem that doesn't fail if input string
     * has unparsed characters after parsing.
     *
     * @param string $string
     *
     * @return array
     *  A [value, parameters] tuple.
     */
    private static function doParseItem(string &$string): array
    {
        return [
            self::parseBareItem($string),
            self::parseParameters($string)
        ];
    }

    /**
     * @param string $string
     *
     * @return bool|float|int|string|\gapple\StructuredHeaders\Bytes|\gapple\StructuredHeaders\Token
     */
    private static function parseBareItem(string &$string)
    {
        $value = null;

        if ($string === "") {
            throw new ParseException();
        } elseif (preg_match('/^(-|\d)/', $string)) {
            $value = self::parseNumber($string);
        } elseif ($string[0] == '"') {
            $value = self::parseString($string);
        } elseif ($string[0] == ':') {
            $value = self::parseByteSequence($string);
        } elseif ($string[0] == '?') {
            $value = self::parseBoolean($string);
        } elseif (preg_match('/^(\*|[a-z])/i', $string)) {
            $value = self::parseToken($string);
        } else {
            throw new ParseException();
        }

        return $value;
    }

    private static function parseParameters(string &$string): object
    {
        $parameters = new \stdClass();

        while (!empty($string) && $string[0] === ';') {
            $string = ltrim(substr($string, 1), ' ');

            $key = self::parseKey($string);
            $parameters->{$key} = true;

            if (!empty($string) && $string[0] === '=') {
                $string = substr($string, 1);
                $parameters->{$key} = self::parseBareItem($string);
            }
        }

        return $parameters;
    }

    private static function parseKey(string &$string): string
    {

        if (preg_match('/^[a-z0-9.*_-]+/', $string, $matches)) {
            $string = substr($string, strlen($matches[0]));

            return $matches[0];
        }

        throw new ParseException();
    }

    private static function parseBoolean(string &$string): bool
    {
        if (!preg_match('/^\?[01]/', $string)) {
            throw new ParseException();
        }

        $value = $string[1] === '1';

        $string = substr($string, 2);

        return $value;
    }

    /**
     * @param string $string
     * @return int|float
     */
    private static function parseNumber(string &$string)
    {
        if (preg_match('/^(-?\d+(?:\.\d+)?)(?:[^\d.]|$)/', $string, $number_matches)) {
            $input_number = $number_matches[1];

            if (preg_match('/^(-?\d{1,12}\.\d{1,3})$/', $input_number, $decimal_matches)) {
                if (strlen($decimal_matches[0]) <= 16) {
                    $string = substr($string, strlen($decimal_matches[0]));

                    return (float) $decimal_matches[0];
                }
            } elseif (preg_match('/^-?\d{1,15}$/', $input_number, $integer_matches)) {
                $string = substr($string, strlen($integer_matches[0]));

                return (int) $integer_matches[0];
            }
        }

        throw new ParseException();
    }

    private static function parseString(string &$string): string
    {
        if (preg_match('/^"([\x00-\x7F]*)"/i', $string, $matches)) {
            $string = substr($string, strlen($matches[1]) + 2);

            // Newlines and Tabs are not allowed; string cannot end in escape character.
            if (preg_match('/(?<!\\\)\\\([nt]|$)/', $matches[1])) {
                throw new ParseException();
            }
            // Only quotes and backslashes should be escaped.
            if (preg_match_all('/(?<!\\\)\\\./', $matches[1], $quoted_matches, PREG_PATTERN_ORDER)) {
                foreach ($quoted_matches[0] as $quoted_match) {
                    if (!in_array($quoted_match, ['\\"', '\\\\'])) {
                        throw new ParseException();
                    }
                }
            }

            // Unescape quotes and backslashes.
            $output_string = preg_replace('/\\\(["\\\])/', '$1', $matches[1]);
        } else {
            throw new ParseException();
        }

        return $output_string;
    }

    private static function parseToken(string &$string): Token
    {
        // Hypertext Transfer Protocol (HTTP/1.1): Message Syntax and Routing
        // 3.2.6. Field Value Components
        // @see https://tools.ietf.org/html/rfc7230#section-3.2.6
        $tchar = preg_quote("!#$%&'*+-.^_`|~");

        if (preg_match('/^((?:\*|[a-z])[a-z0-9:\/' . $tchar . ']*)/i', $string, $matches)) {
            $string = substr($string, strlen($matches[1]));
            return new Token($matches[1]);
        }

        throw new ParseException();
    }

    /**
     * Parse Base64-encoded data.
     *
     * @param string $string
     *
     * @return \gapple\StructuredHeaders\Bytes
     */
    private static function parseByteSequence(string &$string): Bytes
    {
        if (preg_match('/^:([a-z0-9+\/=]*):/i', $string, $matches)) {
            $string = substr($string, strlen($matches[1]) + 2);
            return new Bytes(base64_decode($matches[1]));
        }

        throw new ParseException();
    }
}
