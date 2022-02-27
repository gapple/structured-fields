<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

use Stringable;

class Token implements Stringable
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
