<?php

declare(strict_types=1);

namespace Test;

use Ancarda\Gemini\Gemtext\Encoder\HTML as Encoder;
use Ancarda\Gemini\Gemtext\Encoder\UnsupportedNodeException;
use Ancarda\Gemini\Gemtext\Node\Blockquote;
use Ancarda\Gemini\Gemtext\Node\H1;
use Ancarda\Gemini\Gemtext\Node\H2;
use Ancarda\Gemini\Gemtext\Node\H3;
use Ancarda\Gemini\Gemtext\Node\Link;
use Ancarda\Gemini\Gemtext\Node\ListElement;
use Ancarda\Gemini\Gemtext\Node\Node;
use Ancarda\Gemini\Gemtext\Node\Paragraph;
use Ancarda\Gemini\Gemtext\Node\Preformatted;
use Ancarda\Gemini\Gemtext\Parser;
use ArrayIterator;
use LogicException;
use PHPUnit\Framework\TestCase;

final class HtmlEncoderTest extends TestCase
{
    public function testToHTML(): void
    {
        $encoder = new Encoder();

        self::assertSame(
            [
                '<h1>Introduction to Gemini</h1>',
                '<h2>What is it?</h2>',
                '<p>A new protocol that is</p>',
                '<ul>',
                '<li>Heavier than Gopher</li>',
                '<li>Lighter than the web</li>',
                '</ul>',
                '<h3>Official Website</h3>',
                '<p><a href="gemini://gemini.circumlunar.space/">Gemini Homepage</a></p>',
                '<blockquote>Gemini is really awesome.</blockquote>',
                "<pre>\n&lt;h1&gt;Hello World&lt;/h1&gt;\n</pre>",
            ],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new H1('Introduction to Gemini'),
                new H2('What is it?'),
                new Paragraph('A new protocol that is'),
                new ListElement('Heavier than Gopher'),
                new ListElement('Lighter than the web'),
                new H3('Official Website'),
                new Link('gemini://gemini.circumlunar.space/', 'Gemini Homepage'),
                new Blockquote('Gemini is really awesome.'),
                new Preformatted('<h1>Hello World</h1>'),
            ]))),
        );
    }

    public function testListElementLastInSequenceStillTerminatesUlTag(): void
    {
        $encoder = new Encoder();

        self::assertSame(
            [
                '<ul>',
                '<li>Milk</li>',
                '<li>Eggs</li>',
                '</ul>',
            ],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new ListElement('Milk'),
                new ListElement('Eggs'),
            ]))),
        );
    }

    public function testToHTMLCanOnlyAcceptKnownNodes(): void
    {
        $encoder = new Encoder();
        $bomb = $this->createMock(Node::class);

        try {
            $encoder->encode(new ArrayIterator([$bomb]))->current();
        } catch (UnsupportedNodeException $ex) {
            self::assertSame($bomb, $ex->getNode());
            self::assertSame('Cannot encode node of type ' . get_class($bomb), $ex->getMessage());
            return;
        }

        self::fail('Expected UnsupportedNodeException to be thrown');
    }

    public function testParserCanChangePreCaptionBehavior(): void
    {
        $encoder = new Encoder();

        // By default, pre tags do not have any special handling.
        self::assertSame(
            ["<pre>\n&lt;p&gt;Some HTML&lt;/p&gt;\n</pre>"],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new Preformatted('<p>Some HTML</p>', 'html'),
            ]))),
        );

        // Preformatted text block caption as CSS
        $encoder->setPreCaptionBehavior(Encoder::PRE_CAPTION_AS_CSS);
        self::assertSame(
            ["<pre class=\"html\">\n&lt;p&gt;Some HTML&lt;/p&gt;\n</pre>"],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new Preformatted('<p>Some HTML</p>', 'html'),
            ]))),
        );

        // Preformatted text block caption as aria-label
        $encoder->setPreCaptionBehavior(Encoder::PRE_CAPTION_AS_ARIA_LABEL);
        self::assertSame(
            ["<pre aria-label=\"html\">\n&lt;p&gt;Some HTML&lt;/p&gt;\n</pre>"],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new Preformatted('<p>Some HTML</p>', 'html'),
            ]))),
        );

        // Preformatted text block caption can be both CSS and aria-label
        $encoder->setPreCaptionBehavior(
            Encoder::PRE_CAPTION_AS_CSS | Encoder::PRE_CAPTION_AS_ARIA_LABEL
        );
        self::assertSame(
            ["<pre class=\"html\" aria-label=\"html\">\n&lt;p&gt;Some HTML&lt;/p&gt;\n</pre>"],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new Preformatted('<p>Some HTML</p>', 'html'),
            ]))),
        );

        // Behavior can be set back to normal
        $encoder->reset();
        self::assertSame(
            ["<pre>\n&lt;p&gt;Some HTML&lt;/p&gt;\n</pre>"],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new Preformatted('<p>Some HTML</p>', 'html'),
            ]))),
        );

        // Behavior can also be manually reset
        $encoder->setPreCaptionBehavior(Encoder::PRE_CAPTION_IGNORE);
        self::assertSame(
            ["<pre>\n&lt;p&gt;Some HTML&lt;/p&gt;\n</pre>"],
            iterator_to_array($encoder->encode(new ArrayIterator([
                new Preformatted('<p>Some HTML</p>', 'html'),
            ]))),
        );
    }

    public function testSetPreCaptionBehaviorRejectsInvalidValues(): void
    {
        $encoder = new Encoder();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid behavior code: 123');
        $encoder->setPreCaptionBehavior(123);
    }

    private static function readFile(string $path): string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            self::fail('Failed to read file ' . $path);
        }

        return $contents;
    }

    public function testRealWorldFile(): void
    {
        // Large complex real world document
        // From gemini://gemini.circumlunar.space/docs/gemtext.gmi
        $gemtext = explode("\n", self::readFile(__DIR__ . '/gemtext.gmi'));

        $parser = new Parser();
        $encoder = new Encoder();

        $actual = '';
        foreach ($encoder->encode($parser->parse($gemtext)) as $line) {
            $actual .= $line . "\n";
        }

        self::assertEquals(self::readFile(__DIR__ . '/gemtext.html'), $actual);
    }
}
