<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext\Encoder;

use Ancarda\Gemini\Gemtext\Node\Node;

final class UnsupportedNodeException extends EncoderException
{
    private Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;

        parent::__construct('Cannot encode node of type ' . get_class($node));
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
