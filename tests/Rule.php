<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\Dictionary;
use gapple\StructuredFields\Item;
use gapple\StructuredFields\OuterList;

/**
 * @phpstan-type RuleArray array{
 *      "name": string,
 *      "header_type": "item"|"list"|"dictionary",
 *      "raw"?: string[],
 *      "expected"?: Item|Dictionary|OuterList,
 *      "canonical"?: array{string}|null,
 *      "must_fail"?: bool,
 *      "can_fail"?: bool,
 *    }
 * @phpstan-type RuleObject object{
 *      "name": string,
 *      "header_type": "item"|"list"|"dictionary",
 *      "raw"?: string[],
 *      "expected"?: Item|Dictionary|OuterList,
 *      "canonical"?: array{string}|null,
 *      "must_fail"?: bool,
 *      "can_fail"?: bool,
 *    }
 */
class Rule
{
    public readonly string $name;

    /**
     * @var "item"|"list"|"dictionary"
     */
    public readonly string $header_type;

    /**
     * @var string[]|null
     */
    public readonly ?array $raw;

    public readonly Item|Dictionary|OuterList $expected;
    /**
     * @var array{string}|null
     */
    public readonly ?array $canonical;

    public readonly bool $must_fail;

    public readonly bool $can_fail;

    /**
     * @param RuleArray $properties
     */
    public function __construct(array $properties)
    {
        $properties += [
            'must_fail' => false,
            'can_fail' => false,
        ];

        foreach ($properties as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \RuntimeException('Unknown property in rule definition');
            }
            $this->{$key} = $value;
        }
    }

    /**
     * @param RuleObject $stdClass
     */
    public static function fromClass(object $stdClass): self
    {
        return new self(get_object_vars($stdClass)); // @phpstan-ignore argument.type
    }

    /**
     * @param RuleArray $array
     */
    public static function fromArray(array $array): self
    {
        return new self($array);
    }
}
