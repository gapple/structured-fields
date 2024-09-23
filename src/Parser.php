<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Parser
{
    public static function parseDictionary(string $string): Dictionary
    {
        $value = new Dictionary();

        $input = new ParsingInput($string);
        $input->trim();

        if ($input->empty()) {
            return $value;
        }

        while (true) {
            $key = self::parseKey($input);

            if ($input->isChar('=')) {
                $input->consumeChar();
                $value->{$key} = self::parseItemOrInnerList($input);
            } else {
                // Bare boolean true value.
                $value->{$key} = new Item(true, self::parseParameters($input));
            }

            // Optional whitespace before comma or at end of string.
            $input->trim(true);

            if ($input->empty()) {
                return $value;
            }

            try {
                $input->consumeChar(',');
            } catch (\RuntimeException) {
                throw new ParseException('Expected comma at position ' . $input->position());
            }
            // Optional whitespace after comma.
            $input->trim(true);

            if ($input->empty()) {
                throw new ParseException('Unexpected end of input');
            }
        }
    }

    public static function parseList(string $string): OuterList
    {
        $value = new OuterList();
        $input = new ParsingInput($string);
        $input->trim();

        if ($input->empty()) {
            return $value;
        }

        while (true) {
            $value[] = self::parseItemOrInnerList($input);

            // Optional whitespace before comma or at end of string.
            $input->trim(true);

            if ($input->empty()) {
                return $value;
            }

            try {
                $input->consumeChar(',');
            } catch (\RuntimeException) {
                throw new ParseException('Expected comma at position ' . $input->position());
            }
            // Optional whitespace after comma.
            $input->trim(true);

            if ($input->empty()) {
                throw new ParseException('Unexpected end of input');
            }
        }
    }

    private static function parseItemOrInnerList(ParsingInput $input): TupleInterface
    {
        if ($input->isChar('(')) {
            return self::parseInnerList($input);
        } else {
            return self::doParseItem($input);
        }
    }

    /**
     * @phpstan-impure
     */
    private static function parseInnerList(ParsingInput $input): InnerList
    {
        $startPosition = $input->position();
        $value = [];

        $input->consumeChar('(');
        while (!$input->empty()) {
            $input->trim();

            if ($input->isChar(')')) {
                $input->consumeChar();
                return new InnerList(
                    $value,
                    self::parseParameters($input)
                );
            }

            $value[] = self::doParseItem($input);

            if (!($input->isChar(' ') || $input->isChar(')'))) {
                if ($input->empty()) {
                    break;
                }
                throw new ParseException('Unexpected character in inner list at position ' . $input->position());
            }
        }

        throw new ParseException('Unexpected end of list started at position ' . $startPosition);
    }

    /**
     * @param string $string
     *
     * @return Item
     *  A [value, parameters] tuple.
     */
    public static function parseItem(string $string): Item
    {
        $input = new ParsingInput($string);

        $input->trim();
        if ($input->empty()) {
            throw new ParseException('Unexpected empty input');
        }

        $value = self::doParseItem($input);
        $input->trim();

        if ($input->empty()) {
            return $value;
        }

        throw new ParseException('Unexpected characters at position ' . $input->position());
    }

    /**
     * Internal implementation of parseItem that doesn't fail if input string
     * has remaining characters after parsing.
     *
     * @phpstan-impure
     */
    private static function doParseItem(ParsingInput $input): Item
    {
        return new Item(
            self::parseBareItem($input),
            self::parseParameters($input),
        );
    }

    /**
     * @return bool|float|int|string|Bytes|Date|DisplayString|Token
     *
     * @phpstan-impure
     */
    private static function parseBareItem(ParsingInput $input): mixed
    {
        $char = $input->getChar();
        return match (true) {
            preg_match('/(-|\d)/', $char) === 1  => self::parseNumber($input),
            '"' === $char                        => self::parseString($input),
            preg_match('/[a-z*]/i', $char) === 1 => self::parseToken($input),
            ':' === $char                        => self::parseByteSequence($input),
            '?' === $char                        => self::parseBoolean($input),
            '@' === $char                        => self::parseDate($input),
            '%' === $char                        => self::parseDisplayString($input),
            default => throw new ParseException('Unknown item type at position ' . $input->position()),
        };
    }

    /**
     * @phpstan-impure
     */
    private static function parseParameters(ParsingInput $input): Parameters
    {
        $parameters = new Parameters();
        while ($input->isChar(';')) {
            $input->consumeChar();
            $input->trim();

            $key = self::parseKey($input);
            $parameters->{$key} = true;

            if ($input->isChar('=')) {
                $input->consumeChar();
                $parameters->{$key} = self::parseBareItem($input);
            }
        }

        return $parameters;
    }

    /**
     * @phpstan-impure
     */
    private static function parseKey(ParsingInput $input): string
    {
        try {
            return $input->consumeRegex('/^[a-z*][a-z0-9.*_-]*/');
        } catch (\RuntimeException) {
            throw new ParseException('Invalid key at position ' . $input->position());
        }
    }

    /**
     * @phpstan-impure
     */
    private static function parseBoolean(ParsingInput $input): bool
    {
        $input->consumeChar('?');
        return match ($input->consumeChar()) {
            '0' => false,
            '1' => true,
            default => throw new ParseException('Invalid boolean at position ' . $input->position()),
        };
    }

    /**
     * @phpstan-impure
     */
    private static function parseNumber(ParsingInput $input): int|float
    {
        $startPosition = $input->position();
        try {
            $number = $input->consumeRegex('/^(-?\d+(?:\.\d+)?)/');
        } catch (\RuntimeException) {
            throw new ParseException('Invalid number format at position ' . $startPosition);
        }

        if (preg_match('/^-?\d{1,12}\.\d{1,3}$/', $number)) {
            return (float) $number;
        } elseif (preg_match('/^-?\d{1,15}$/', $number)) {
            return (int) $number;
        }
        throw new ParseException('Number contains too many digits at position ' . $startPosition);
    }

    /**
     * @phpstan-impure
     */
    private static function parseString(ParsingInput $input): string
    {
        $output = '';

        $input->consumeChar('"');
        while (!$input->empty()) {
            $char = $input->consumeChar();

            if ($char === '\\') {
                if ($input->empty()) {
                    throw new ParseException("Invalid end of string");
                }

                $char = $input->consumeChar();
                if ($char !== '"' && $char !== '\\') {
                    throw new ParseException(
                        'Invalid escaped character in string at position ' . ($input->position() - 1)
                    );
                }
            } elseif ($char === '"') {
                return $output;
            } elseif (ord($char) <= 0x1f || ord($char) >= 0x7f) {
                throw new ParseException('Invalid character in string at position ' . ($input->position() - 1));
            }

            $output .= $char;
        }

        throw new ParseException("Invalid end of string");
    }

    /**
     * @phpstan-impure
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private static function parseDisplayString(ParsingInput $string): DisplayString
    {
        $startPosition = $string->position();
        try {
            $string->consumeString('%"');
        } catch (\RuntimeException) {
            throw new ParseException('Invalid start of display string at position ' . $startPosition);
        }

        $encoded_string = '';
        while (!$string->empty()) {
            $char = $string->consumeChar();

            if (ord($char) <= 0x1f || ord($char) >= 0x7f) {
                throw new ParseException(
                    'Invalid character in display string at position ' . ($string->position() - 1)
                );
            } elseif ($char === '%') {
                try {
                    $encoded_string .= '%' . $string->consumeRegex('/^[0-9a-f]{2}/');
                } catch (\RuntimeException) {
                    throw new ParseException(
                        'Invalid hex values in display string at position ' . ($string->position() - 1)
                    );
                }
            } elseif ($char === '"') {
                $display_string = new DisplayString(rawurldecode($encoded_string));
                // An invalid UTF-8 subject will cause the preg_* function to match nothing.
                // @see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
                if (!preg_match('/^\X*$/u', (string) $display_string)) {
                    throw new ParseException('Invalid byte sequence in display string at position ' . $startPosition);
                }
                return $display_string;
            } else {
                $encoded_string .= $char;
            }
        }

        throw new ParseException('Invalid end of display string started at position ' . $startPosition);
    }

    /**
     * @phpstan-impure
     */
    private static function parseToken(ParsingInput $input): Token
    {
        // Hypertext Transfer Protocol (HTTP/1.1): Message Syntax and Routing
        // 3.2.6. Field Value Components
        // @see https://tools.ietf.org/html/rfc7230#section-3.2.6
        $tchar = preg_quote("!#$%&'*+-.^_`|~");

        // parseToken is only called by parseBareItem if the initial character
        // is valid, so a Token object is always returned.  If there is an
        // invalid character in the token, the public function that was called
        // will detect that the remainder of the input string is invalid.
        return new Token($input->consumeRegex('/^([a-z*][a-z0-9:\/' . $tchar . ']*)/i'));
    }

    /**
     * Parse Base64-encoded data.
     *
     * @phpstan-impure
     */
    private static function parseByteSequence(ParsingInput $input): Bytes
    {
        $startPosition = $input->position();
        $input->consumeChar(':');
        try {
            $bytes = $input->consumeRegex('/^([a-z0-9+\/=]*)/i');
            $input->consumeChar(':');
            return new Bytes(base64_decode($bytes));
        } catch (\RuntimeException) {
            throw new ParseException('Invalid byte sequence at position ' . $startPosition);
        }
    }

    /**
     * @phpstan-impure
     */
    private static function parseDate(ParsingInput $input): Date
    {
        $startPosition = $input->position();
        $input->consumeChar('@');
        try {
            $value = self::parseNumber($input);

            if (is_int($value)) {
                return new Date($value);
            }
        } catch (ParseException) {
        }

        throw new ParseException('Invalid Date format at position ' . $startPosition);
    }
}
