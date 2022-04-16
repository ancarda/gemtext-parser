<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext;

use Generator;

interface ParserInterface
{
    /**
     * @param iterable<string> $lines
     * @return Generator<Node\Node>
     */
    public function parse(iterable $lines): Generator;
}
