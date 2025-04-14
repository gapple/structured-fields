<?php

namespace gapple\Tests\StructuredFields;

use gapple\StructuredFields\ParsingInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresSetting;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParsingInput::class)]
class ParsingInputTest extends TestCase
{
    public function testEmpty(): void
    {
        $input = new ParsingInput('');
        $this->assertTrue($input->empty());

        $this->expectException(\RuntimeException::class);
        $input->getChar();
    }

    public function testParseString(): void
    {
        $input = new ParsingInput('Test');

        $this->assertEquals(0, $input->position());
        $this->assertEquals(4, $input->remainingLength());

        $this->assertEquals('T', $input->consumeChar());

        $this->assertEquals(1, $input->position());
        $this->assertEquals(3, $input->remainingLength());
        $this->assertEquals('est', $input->remaining());
        $this->assertTrue($input->isNextChar('e'));

        // getChar() should not change pointer position.
        $this->assertEquals('e', $input->getChar());
        $this->assertEquals(3, $input->remainingLength());
        $this->assertEquals('est', $input->remaining());

        $this->assertFalse($input->skipNextCharIf('s'));
        $this->assertEquals(3, $input->remainingLength());
        $this->assertEquals('est', $input->remaining());

        $this->assertTrue($input->skipNextCharIf('e'));
        $this->assertEquals(2, $input->remainingLength());
        $this->assertEquals('st', $input->remaining());
    }

    /**
     * @return array<string, array{string, bool, string}>
     */
    public static function trimProvider(): array
    {
        return [
            // [input string, trim optional white space, expected remaining string]
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

    /**
     * @return array<string, array{string, bool}>
     */
    public static function regexAssertionProvider(): array
    {
        return [
            'Valid' => ['/^test/', true],
            'Valid with modifier' => ['/^test/i', true],
            'Missing start anchor' => ['/test/', false],
            'End anchor' => ['/test$/', false],
            'End anchor and modifier' => ['/test$/i', false],
        ];
    }

    #[RequiresSetting('zend.assertions', '1')]
    #[DataProvider('regexAssertionProvider')]
    public function testRegexAssertions(string $regex, bool $expected): void
    {
        try {
            $input = new ParsingInput('test');
            $result = $input->consumeRegex($regex);

            if (!$expected) {
                $this->fail('Expression should not have passed assertions');
            } else {
                $this->assertEquals('test', $result);
            }
        } catch (\AssertionError $e) {
            if ($expected) {
                $this->fail('Expression failed assertion: ' . $e->getMessage());
            } else {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testRegexMatched(): void
    {
        $input = new ParsingInput('test');

        $this->expectException(\RuntimeException::class);
        $this->assertEquals(
            'te',
            $input->consumeRegex('/^t.'),
        );
    }

    public function testRegexNotMatched(): void
    {
        $input = new ParsingInput('test');

        $this->expectException(\RuntimeException::class);
        $input->consumeRegex('/^foo');
    }
}
