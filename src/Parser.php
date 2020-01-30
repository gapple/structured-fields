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
        return [];
    }

    /**
     * @param string $string
     *
     * @return array
     *  A [value, parameters] tuple.
     */
    public static function parseItem(string $string): array
    {
        return [
            self::parseBareItem($string),
            self::parseParameters($string)
        ];
    }

    /**
     * @param string $string
     *
     * @return bool|float|int|string
     */
    private static function parseBareItem(string &$string)
    {
        $string = ltrim($string);
        $value = null;

        if ($string === "") {
            throw new ParseException();
        } elseif (preg_match('/^(-|\d)/', $string)) {
            $value = self::parseNumber($string);
        } elseif ($string[0] == '"') {
            $value = self::parseString($string);
        } elseif ($string[0] == '?') {
            $value = self::parseBoolean($string);
        } elseif (preg_match('/^(\*|[a-z])/i', $string)) {
            $value = self::parseToken($string);
        } else {
            throw new ParseException();
        }

        $string = ltrim($string);

        if (!empty($string)) {
            throw new ParseException();
        }

        return $value;
    }

    private static function parseParameters(string &$string): object
    {
        $parameters = new \stdClass();

        if (!empty($string) && $string[0] === ';') {
            $string = ltrim(substr(1, $string));
        }

        return $parameters;
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

    private static function parseToken(string &$string): string
    {
        // Hypertext Transfer Protocol (HTTP/1.1): Message Syntax and Routing
        // 3.2.6. Field Value Components
        // @see https://tools.ietf.org/html/rfc7230#section-3.2.6
        $tchar = preg_quote("!#$%&'*+-.^_`|~");

        if (preg_match('/^((?:\*|[a-z])[a-z0-9:\/' . $tchar . ']+)/i', $string, $matches)) {
            $string = substr($string, strlen($matches[1]));
        } else {
            throw new ParseException();
        }

        return new Token($matches[1]);
    }
}
