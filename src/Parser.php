<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext;

use Generator;

final class Parser implements ParserInterface
{
    public function parse(iterable $lines): Generator
    {
        $inPreBlock = false;
        $preContents = '';
        $preCaption = null;

        foreach ($lines as $line) {
            if (str_starts_with($line, '```')) {
                if ($inPreBlock) {
                    // This is the end of the pre block, yield everything collected so far.
                    // The last byte of preContents is ignored as it is just a newline character.
                    yield new Node\Preformatted(substr($preContents, 0, -1), $preCaption);
                    $preContents = '';
                } else {
                    // This is the start of the pre block. Extract the caption if there is one.
                    $cap = substr($line, 3);
                    $preCaption = strlen($cap) === 0 ? null : $cap;
                }
                $inPreBlock = !$inPreBlock;
            } elseif ($inPreBlock) {
                $preContents .= $line . "\n";
            } elseif (str_starts_with($line, '# ')) {
                yield new Node\H1(substr($line, 2));
            } elseif (str_starts_with($line, '## ')) {
                yield new Node\H2(substr($line, 3));
            } elseif (str_starts_with($line, '### ')) {
                yield new Node\H3(substr($line, 4));
            } elseif (str_starts_with($line, '>')) {
                yield new Node\Blockquote(substr($line, 1));
            } elseif (str_starts_with($line, '* ')) {
                yield new Node\ListElement(substr($line, 2));
            } elseif (str_starts_with($line, '=>')) {
                // Lop off `=>', then try to extract the label if there is one.
                // Labels are everything after the first space.
                $line = trim(substr($line, 2));
                $space = strpos($line, ' ');
                if ($space === false) {
                    yield new Node\Link($line);
                } else {
                    yield new Node\Link(substr($line, 0, $space), trim(substr($line, $space)));
                }
            } else {
                yield new Node\Paragraph($line);
            }
        }
    }
}
