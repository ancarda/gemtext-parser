<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext\Encoder;

use Ancarda\Gemini\Gemtext\Node\Node;

interface EncoderInterface
{
    /**
     * @param iterable<Node> $nodes
     * @throws EncoderException
     * @return \Generator<string>
     */
    public function encode(iterable $nodes): \Generator;
}
