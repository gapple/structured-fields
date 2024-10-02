<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

class Date
{
    public function __construct(private readonly int $value)
    {
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
