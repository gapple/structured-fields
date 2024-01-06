<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

class Date
{
    /**
     * @var int
     * @readonly
     */
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
