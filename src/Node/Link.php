<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext\Node;

/**
 * Hyperlink
 */
final class Link extends Node
{
    private string $link;
    private ?string $label;

    public function __construct(string $link, ?string $label = null)
    {
        $this->link = $link;
        $this->label = $label;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
