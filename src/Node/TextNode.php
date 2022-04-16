<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext\Node;

/**
 * A node that just contains text, such as Paragraph or H1. More complex Nodes
 * such as Link and Preformatted will not have a getText method to make it
 * clear which component is which.
 */
abstract class TextNode extends Node
{
    protected string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
