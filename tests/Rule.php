<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Dictionary;
use gapple\StructuredFields\Item;
use gapple\StructuredFields\OuterList;

class Rule
{
    /**
     * @var string
     * @readonly
     */
    public $name;

    /**
     * @var "item"|"list"|"dictionary"
     * @readonly
     */
    public $header_type;

    /**
     * @var string[]|null
     * @readonly
     */
    public $raw;

    /**
     * @var null|Item|Dictionary|OuterList
     * @readonly
     */
    public $expected;

    /**
     * @var array{string}|null
     * @readonly
     */
    public $canonical;

    /**
     * @var bool
     * @readonly
     */
    public $must_fail;

    /**
     * @var bool
     * @readonly
     */
    public $can_fail;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(array $properties)
    {
        $this->must_fail = false;
        $this->can_fail = false;

        foreach ($properties as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \RuntimeException('Unknown property in rule definition');
            }
            $this->{$key} = $value;
        }
    }

    public static function fromClass(object $stdClass): self
    {
        return new self(get_object_vars($stdClass));
    }

    /**
     * @param array<string, mixed> $array
     */
    public static function fromArray(array $array): self
    {
        return new self($array);
    }
}
