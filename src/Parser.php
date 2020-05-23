<?php

namespace gapple\StructuredHeaders;

class Parser
{

    public static function parseDictionary(string $string): \stdClass
    {
        $value = new \stdClass();

        $string = ltrim($string, ' ');

        while (!empty($string)) {
            $key = self::parseKey($string);

            if (!empty($string) && $string[0] === '=') {
                $string = substr($string, 1);
                $value->{$key} = self::parseItemOrInnerList($string);
            } else {
                // Bare boolean true value.
                $value->{$key} = [true, self::parseParameters($string)];
            }

            // OWS (optional whitespace) before comma.
            // @see https://tools.ietf.org/html/rfc7230#section-3.2.3
            $string = ltrim($string, " \t");

            if (empty($string)) {
                return $value;
            }

            // OWS (optional whitespace) after comma.
            if (!preg_match('/^(,[ \t]*)/', $string, $comma_matches)) {
                throw new ParseException('Expected comma');
            }

            $string = substr($string, strlen($comma_matches[1]));

            if (empty($string)) {
                throw new ParseException('Unexpected end of input');
            }
        }

        return $value;
    }

    public static function parseList(string $string): array
    {
        $value = [];

        $string = ltrim($string, ' ');

        while (!empty($string)) {
            $value[] = self::parseItemOrInnerList($string);

            // OWS (optional whitespace) before comma.
            // @see https://tools.ietf.org/html/rfc7230#section-3.2.3
            $string = ltrim($string, " \t");

            if (empty($string)) {
                return $value;
            }

            // OWS (optional whitespace) after comma.
            if (!preg_match('/^(,[ \t]*)/', $string, $comma_matches)) {
                throw new ParseException('Expected comma');
            }

            $string = substr($string, strlen($comma_matches[1]));

            if (empty($string)) {
                throw new ParseException('Unexpected end of input');
            }
        }

        return $value;
    }

    private static function parseItemOrInnerList(string &$string): array
    {
        if ($string[0] === '(') {
            return self::parseInnerList($string);
        } else {
            return self::doParseItem($string);
        }
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
                throw new ParseException('Unexpected character in inner list');
            }
        }

        throw new ParseException('Unexpected end of input');
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

        throw new ParseException('Unexpected characters at end of input');
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
            throw new ParseException('Unexpected empty input');
        } elseif (preg_match('/^(-|\d)/', $string)) {
            $value = self::parseNumber($string);
        } elseif ($string[0] == '"') {
            $value = self::parseString($string);
        } elseif ($string[0] == ':') {
            $value = self::parseByteSequence($string);
        } elseif ($string[0] == '?') {
            $value = self::parseBoolean($string);
        } elseif (preg_match('/^([a-z*])/i', $string)) {
            $value = self::parseToken($string);
        } else {
            throw new ParseException('Unknown item type');
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
        if (preg_match('/^[a-z*][a-z0-9.*_-]*/', $string, $matches)) {
            $string = substr($string, strlen($matches[0]));

            return $matches[0];
        }

        throw new ParseException('Invalid character in key');
    }

    private static function parseBoolean(string &$string): bool
    {
        if (!preg_match('/^\?[01]/', $string)) {
            throw new ParseException('Invalid character in boolean');
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
                $string = substr($string, strlen($decimal_matches[0]));

                return (float) $decimal_matches[0];
            } elseif (preg_match('/^-?\d{1,15}$/', $input_number, $integer_matches)) {
                $string = substr($string, strlen($integer_matches[0]));

                return (int) $integer_matches[0];
            } else {
                throw new ParseException('Number contains too many digits');
            }
        }

        throw new ParseException('Invalid number format');
    }

    private static function parseString(string &$string): string
    {
        if (preg_match('/^"([\x00-\x7F]*)"/i', $string, $matches)) {
            $string = substr($string, strlen($matches[1]) + 2);

            // Newlines and Tabs are not allowed; string cannot end in escape character.
            if (preg_match('/(?<!\\\)\\\([nt]|$)/', $matches[1])) {
                throw new ParseException('Invalid whitespace in string');
            }
            // Only quotes and backslashes should be escaped.
            if (preg_match_all('/(?<!\\\)\\\./', $matches[1], $quoted_matches, PREG_PATTERN_ORDER)) {
                foreach ($quoted_matches[0] as $quoted_match) {
                    if (!in_array($quoted_match, ['\\"', '\\\\'])) {
                        throw new ParseException('Invalid escaped character in string');
                    }
                }
            }

            // Unescape quotes and backslashes.
            $output_string = preg_replace('/\\\(["\\\])/', '$1', $matches[1]);
        } else {
            throw new ParseException('Invalid character in string');
        }

        return $output_string;
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

        throw new ParseException('Invalid character in byte sequence');
    }
}
