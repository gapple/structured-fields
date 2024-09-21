<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

/**
 * @internal
 */
class ParsingInput
{
    private int $position = 0;

    public function __construct(
        private readonly string $value,
    ) {
    }

    public function position(): int
    {
        return $this->position;
    }

    public function empty(): bool
    {
        return $this->position >= strlen($this->value);
    }

    public function remaining(): string
    {
        return substr($this->value, $this->position);
    }

    /**
     * Trim whitespace from beginning of string.
     *
     * @param bool $ows
     *   Whether all Optional Whitespace characters should be trimmed.  If false, only space characters are trimmed.
     *   @see https://tools.ietf.org/html/rfc7230#section-3.2.3
     * @return void
     */
    public function trim(bool $ows = false): void
    {
        $this->consumeRegex('/^[' . ($ows ? ' \t' : ' ') . ']*/');
    }

    public function getChar(): string
    {
        if ($this->empty()) {
            throw new \RuntimeException('Reached end of value');
        }
        return $this->value[$this->position];
    }

    /**
     * @phpstan-impure
     */
    public function consume(int $length, string $expected = null): string
    {
        assert($length > 0);
        assert($expected === null || strlen($expected) === $length);

        if ($length > strlen($this->value) - $this->position) {
            throw new \RuntimeException('Reached end of value');
        }

        $output = substr($this->value, $this->position, $length);
        if (!is_null($expected) && $expected !== $output) {
            throw new \RuntimeException('Unexpected character');
        }
        $this->position += $length;
        return $output;
    }

    /**
     * @phpstan-impure
     */
    public function consumeChar(string $value = null): string
    {
        assert($value === null || strlen($value) === 1);

        return $this->consume(1, $value);
    }

    /**
     * @phpstan-impure
     */
    public function consumeString(string $value): void
    {
        $this->consume(strlen($value), $value);
    }

    /**
     * @phpstan-impure
     */
    public function consumeRegex(string $pattern): string
    {
        assert(str_starts_with($pattern, '/^'));

        if (preg_match($pattern, $this->remaining(), $matches)) {
            $this->position += strlen($matches[0]);
            return $matches[0];
        }

        throw new \RuntimeException();
    }
}
