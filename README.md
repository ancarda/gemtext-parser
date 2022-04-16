# ancarda/gemtext-parser

_Gemtext (text/gemini) parser and HTML encoder_

[![License](https://img.shields.io/badge/license-MIT-teal)](https://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/ancarda/gemtext-parser/v/stable)](https://packagist.org/packages/ancarda/gemtext-parser)
[![Total Downloads](https://poser.pugx.org/ancarda/gemtext-parser/downloads)](https://packagist.org/packages/ancarda/gemtext-parser)
[![builds.sr.ht status](https://builds.sr.ht/~ancarda/gemtext-parser.svg)](https://builds.sr.ht/~ancarda/gemtext-parser?)

This package implements a PHP parser for Gemtext (`text/gemini`) as specified
here: https://gemini.circumlunar.space/docs/gemtext.gmi

## Useful Links

* Source Code:   <https://git.sr.ht/~ancarda/gemtext-parser/>
* Issue Tracker: <https://todo.sr.ht/~ancarda/gemtext-parser/>
* Mailing List:  <https://lists.sr.ht/~ancarda/gemtext-parser/>

## Usage

All the low level classes are built around `Generator`, which makes plugging in
middleware easy while keeping memory usage low.

Unfortunately, Generators can be a bit of work to actually use. As such, a 
utility class, `SimpleTransformer` is available which abstracts this away if
you just want a Gemtext to HTML conversion quickly and easily.

Here's how to convert Gemtext to HTML with the low level (`Generator`) API:

```php
<?php

$parser  = new Ancarda\Gemini\Gemtext\Parser;
$encoder = new Ancarda\Gemini\Gemtext\Encoder\HTML;

$nodes = $parser->parse(explode("\n", $gemtext));

$html = implode("\n", iterator_to_array($encoder->encode($nodes)));
```

And here's the higher level utility class, which abstracts this away:

```php
<?php

$transformer = new \Ancarda\Gemini\Gemtext\Util\SimpleTransformer;

echo $transformer->transform($gemText);
```

### Pipelining

You can create lightweight middleware by creating a function that accepts and
returns `Generator<Node>`. This function would be inserted between `encode` and
`parse`, like so:

```
$encoder->encode($middleware($parser->parse(explode("\n", $gemtext))));
```

Here, middleware's `__invoke` method accepts and returns `Generator<Node>`.
Middleware could make modifications, inject new nodes, drop some nodes, and so
on. For instance:

```php
<?php

$reverse_paragraphs = new class {
    public function __invoke(Generator $nodes): Generator
    {
        foreach ($nodes as $node) {
            if ($node instanceof Paragraph) {
                yield new Paragraph(strrev($node->getText()));
            } else {
                yield $node;
            }
        }
    }
};
```
