<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext\Node;

use Ancarda\Gemini\Gemtext\Node;

/**
 * Preformatted textblock with optional caption
 */
final class Preformatted extends Node\Node
{
    private string $contents;
    private ?string $caption;

    public function __construct(string $contents, ?string $caption = null)
    {
        $this->contents = $contents;
        $this->caption = $caption;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }
}
