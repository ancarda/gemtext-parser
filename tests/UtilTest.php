<?php

declare(strict_types=1);

namespace Test;

use Ancarda\Gemini\Gemtext\Encoder\EncoderInterface;
use Ancarda\Gemini\Gemtext\Node\H1;
use Ancarda\Gemini\Gemtext\Node\Paragraph;
use Ancarda\Gemini\Gemtext\ParserInterface;
use Ancarda\Gemini\Gemtext\Util\SimpleTransformer;
use Generator;
use PHPUnit\Framework\TestCase;

final class UtilTest extends TestCase
{
    public function testTransformer(): void
    {
        // Default configuration (Gemtext -> HTML)
        $transformer = new SimpleTransformer();
        self::assertEquals(
            "<h1>Hello World</h1>",
            $transformer->transform("# Hello World")
        );

        // Debug/dump what is being encoded
        $transformer->setEncoder(new class implements EncoderInterface {
            public function encode(iterable $nodes): Generator
            {
                foreach ($nodes as $node) {
                    yield get_class($node);
                }
            }
        });
        self::assertEquals(
            H1::class,
            $transformer->transform("# Hello World")
        );

        // Custom parser that forces everything to be a Paragraph
        $transformer->setParser(new class implements ParserInterface {
            public function parse(iterable $lines): Generator
            {
                foreach ($lines as $line) {
                    yield new Paragraph($line);
                }
            }
        });
        self::assertEquals(
            Paragraph::class,
            $transformer->transform("# Hello World")
        );
    }
}
