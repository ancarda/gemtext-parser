<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext\Encoder;

use Ancarda\Gemini\Gemtext\Node\Blockquote;
use Ancarda\Gemini\Gemtext\Node\H1;
use Ancarda\Gemini\Gemtext\Node\H2;
use Ancarda\Gemini\Gemtext\Node\H3;
use Ancarda\Gemini\Gemtext\Node\Link;
use Ancarda\Gemini\Gemtext\Node\ListElement;
use Ancarda\Gemini\Gemtext\Node\Paragraph;
use Ancarda\Gemini\Gemtext\Node\Preformatted;
use Generator;
use LogicException;

final class HTML implements EncoderInterface
{
    public const PRE_CAPTION_IGNORE = 0;
    public const PRE_CAPTION_AS_CSS = 1;
    public const PRE_CAPTION_AS_ARIA_LABEL = 2;

    private int $preCaptionBehavior = self::PRE_CAPTION_IGNORE;

    /**
     * @param int $flags Bitwise number made up using PRE_CAPTION_
     * @throws LogicException Invalid number
     */
    public function setPreCaptionBehavior(int $flags): void
    {
        if ($flags < 0 || $flags > 3) {
            throw new LogicException('Invalid behavior code: ' . $flags);
        }

        $this->preCaptionBehavior = $flags;
    }

    /**
     * Reset generator back to initial configuration
     */
    public function reset(): void
    {
        $this->preCaptionBehavior = self::PRE_CAPTION_IGNORE;
    }

    public function encode(iterable $nodes): Generator
    {
        $inList = false;

        foreach ($nodes as $node) {
            $class = get_class($node);

            // Outside of a list, an initial ListElement will cause a <ul> to emerge.
            if ($class === ListElement::class && !$inList) {
                $inList = true;
                yield '<ul>';
            }

            // Inside a list, anything other than a ListElement will terminate the <ul>
            if ($inList && $class !== ListElement::class) {
                $inList = false;
                yield '</ul>';
            }

            yield match ($class) {
                // Text
                Paragraph::class => '<p>' . htmlspecialchars($node->getText(), ENT_HTML5) . '</p>',

                // Links
                Link::class => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    htmlspecialchars($node->getLink(), ENT_HTML5),
                    htmlspecialchars($node->getLabel() === null ? $node->getLink() : $node->getLabel(), ENT_HTML5),
                ),

                // Headings
                H1::class => '<h1>' . htmlspecialchars($node->getText(), ENT_HTML5) . '</h1>',
                H2::class => '<h2>' . htmlspecialchars($node->getText(), ENT_HTML5) . '</h2>',
                H3::class => '<h3>' . htmlspecialchars($node->getText(), ENT_HTML5) . '</h3>',

                // Lists
                ListElement::class => '<li>' . htmlspecialchars($node->getText(), ENT_HTML5) . '</li>',

                // Blockquotes
                Blockquote::class => '<blockquote>' . htmlspecialchars($node->getText(), ENT_HTML5) . '</blockquote>',

                // Preformatted Text
                Preformatted::class => self::preformatted($node),

                default => throw new UnsupportedNodeException($node),
            };
        }

        // If the very last node is a ListElement, the <ul> tag must be closed.
        if ($inList) {
            yield '</ul>';
        }
    }

    private function preformatted(Preformatted $pre): string
    {
        $tag = '';

        if (($this->preCaptionBehavior & self::PRE_CAPTION_AS_CSS) > 0) {
            $tag = ' class="' . $pre->getCaption() . '"';
        }

        if (($this->preCaptionBehavior & self::PRE_CAPTION_AS_ARIA_LABEL) > 0) {
            $tag .= ' aria-label="' . $pre->getCaption() . '"';
        }

        return sprintf(
            "<pre%s>\n%s\n</pre>",
            $tag,
            htmlspecialchars($pre->getContents(), ENT_HTML5)
        );
    }
}
