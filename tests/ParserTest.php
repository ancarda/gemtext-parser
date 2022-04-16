<?php

declare(strict_types=1);

namespace Test;

use Ancarda\Gemini\Gemtext\Node\Blockquote;
use Ancarda\Gemini\Gemtext\Node\H1;
use Ancarda\Gemini\Gemtext\Node\H2;
use Ancarda\Gemini\Gemtext\Node\H3;
use Ancarda\Gemini\Gemtext\Node\Link;
use Ancarda\Gemini\Gemtext\Node\ListElement;
use Ancarda\Gemini\Gemtext\Node\Paragraph;
use Ancarda\Gemini\Gemtext\Parser;
use Ancarda\Gemini\Gemtext\Node\Preformatted;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    /**
     * @return iterable<string>
     */
    private static function lines(string $text): iterable
    {
        return explode("\n", $text);
    }

    public function testParser(): void
    {
        $parser = new Parser();

        // Text
        self::assertEquals(
            [
                new Paragraph('Line 1'),
                new Paragraph(''),
                new Paragraph('Line 2'),
            ],
            iterator_to_array($parser->parse(self::lines("Line 1\n\nLine 2"))),
        );
        $p = $parser->parse(self::lines('Hello World'))->current();
        self::assertInstanceOf(Paragraph::class, $p);
        self::assertSame('Hello World', $p->getText());

        // Links
        $link = $parser->parse(self::lines('=> gemini://gemini.circumlunar.space/ Gemini Homepage'))->current();
        self::assertInstanceOf(Link::class, $link);
        self::assertSame('gemini://gemini.circumlunar.space/', $link->getLink());
        self::assertSame('Gemini Homepage', $link->getLabel());

        $link = $parser->parse(self::lines('=> gemini://markdain.net'))->current();
        self::assertInstanceOf(Link::class, $link);
        self::assertSame('gemini://markdain.net', $link->getLink());
        self::assertNull($link->getLabel());

        // Whitespace after => is strictly optinal
        $link = $parser->parse(self::lines('=>gemini://nowhitespace.invalid'))->current();
        self::assertInstanceOf(Link::class, $link);
        self::assertSame('gemini://nowhitespace.invalid', $link->getLink());
        self::assertNull($link->getLabel());

        // Headings
        $h1 = $parser->parse(self::lines('# First Level'))->current();
        self::assertInstanceOf(H1::class, $h1);
        self::assertSame('First Level', $h1->getText());

        $h2 = $parser->parse(self::lines('## Second Level'))->current();
        self::assertInstanceOf(H2::class, $h2);
        self::assertSame('Second Level', $h2->getText());

        $h3 = $parser->parse(self::lines('### Third Level'))->current();
        self::assertInstanceOf(H3::class, $h3);
        self::assertSame('Third Level', $h3->getText());

        // Lists
        $li = $parser->parse(self::lines('* List Element'))->current();
        self::assertInstanceOf(ListElement::class, $li);
        self::assertSame('List Element', $li->getText());
        self::assertEquals(
            [new ListElement('Milk'), new ListElement('Cheese')],
            iterator_to_array($parser->parse(self::lines("* Milk\n* Cheese"))),
        );

        // Blockquotes
        $bq = $parser->parse(self::lines('>Anything worth doing is worth doing well'))->current();
        self::assertInstanceOf(Blockquote::class, $bq);
        self::assertSame('Anything worth doing is worth doing well', $bq->getText());

        // Preformatted Text
        $pre = $parser->parse(self::lines("```html\n<h1>Hello World</h1>\n```"))->current();
        self::assertInstanceOf(Preformatted::class, $pre);
        self::assertSame('html', $pre->getCaption());
        self::assertSame('<h1>Hello World</h1>', $pre->getContents());
    }

    public function testParserCanHandleGemtextInsidePre(): void
    {
        $parser = new Parser();

        self::assertEquals(
            [new Preformatted('=> gemini://nowhere')],
            iterator_to_array($parser->parse(self::lines("```\n=> gemini://nowhere\n```")))
        );
    }

    public function testPreTagsDoNotCrossContaminate(): void
    {
        $parser = new Parser();

        self::assertEquals(
            [
                new Preformatted('1'),
                new Paragraph('p'),
                new Preformatted('2'),
            ],
            iterator_to_array($parser->parse(self::lines(<<<EOF
```
1
```
p
```
2
```
EOF
            )))
        );
    }
}
