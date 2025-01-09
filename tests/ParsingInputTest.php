<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\ParsingInput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParsingInputTest extends TestCase
{
    public function testGetEmptyChar(): void
    {
        $input = new ParsingInput('');
        $this->expectException(\RuntimeException::class);
        $input->getChar();
    }

    /**
     * @return array<string, array{string, bool, string}>
     */
    public static function trimProvider(): array
    {
        return [
            'space' => ['  test ', false, 'test '],
            'ows' => [" \t test ", true, 'test '],
            'non-ows' => [" \t test ", false, "\t test "],
        ];
    }

    #[DataProvider('trimProvider')]
    public function testTrim(string $value, bool $ows, string $expected): void
    {
        $input = new ParsingInput($value);
        $input->trim($ows);

        $this->assertEquals($expected, $input->remaining());
    }

    public function testConsumeExcess(): void
    {
        $input = new ParsingInput('test');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Reached end of value');
        $input->consume(5);
    }

    public function testConsumeNotMatched(): void
    {
        $input = new ParsingInput('test');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected character');
        $input->consumeString('foo');
    }
}
