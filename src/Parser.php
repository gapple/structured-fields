<?php

namespace gapple\StructuredHeaders;

class Parser
{

    public static function parseDictionary(string $string)
    {
    }

    public static function parseList(string $string)
    {
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
    }

    private static function parseToken(string &$string): string
    {
    }
}
