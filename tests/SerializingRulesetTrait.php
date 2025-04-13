<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\SerializeException;
use gapple\StructuredFields\Serializer;
use PHPUnit\Framework\Attributes\DataProvider;

trait SerializingRulesetTrait
{
    /**
     * @return array<string, array{Rule}>
     */
    public static function serializeRulesetDataProvider(): array
    {
        return array_filter(
            static::rulesetDataProvider(),
            fn($params) => !empty($params[0]->expected)
        );
    }

    #[DataProvider('serializeRulesetDataProvider')]
    public function testSerializing(Rule $record): void
    {
        if (array_key_exists($record->name, $this->skipSerializingRules)) {
            $this->markTestSkipped(
                'Skipped "' . $record->name . '": ' . $this->skipSerializingRules[$record->name]
            );
        }

        try {
            $serializedValue = Serializer::{'serialize' . ucfirst($record->header_type)}($record->expected);

            if ($record->must_fail) {
                $this->fail('"' . $record->name . '" must fail serializing');
            }

            $this->assertEquals(
                implode(', ', $record->canonical ?? $record->raw),
                $serializedValue,
                '"' . $record->name . '" was not serialized to expected value'
            );
        } catch (SerializeException $e) {
            if ($record->must_fail) {
                $this->addToAssertionCount(1);
                return;
            } else {
                $this->fail('"' . $record->name . '"  failed serializing with exception: ' . $e->getMessage());
            }
        }
    }
}
