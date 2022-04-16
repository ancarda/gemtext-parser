<?php

declare(strict_types=1);

namespace Ancarda\Gemini\Gemtext\Util;

use Ancarda\Gemini\Gemtext\Encoder\EncoderInterface;
use Ancarda\Gemini\Gemtext\Encoder\HTML;
use Ancarda\Gemini\Gemtext\Parser;
use Ancarda\Gemini\Gemtext\ParserInterface;

/**
 * Simple utility class that makes the primary use-case of parser->encoder easy.
 *
 * This class defaults to Gemini->HTML but is configurable through the setters.
 */
final class SimpleTransformer
{
    private ParserInterface $parser;
    private EncoderInterface $encoder;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->encoder = new HTML();
    }

    public function setParser(ParserInterface $parser): void
    {
        $this->parser = $parser;
    }

    public function setEncoder(EncoderInterface $encoder): void
    {
        $this->encoder = $encoder;
    }

    public function transform(string $gemtext): string
    {
        return implode("\n", iterator_to_array(
            $this->encoder->encode(
                $this->parser->parse(explode("\n", $gemtext))
            )
        ));
    }
}
