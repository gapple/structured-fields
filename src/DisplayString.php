<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

class DisplayString
{
    public function __construct(private readonly string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
