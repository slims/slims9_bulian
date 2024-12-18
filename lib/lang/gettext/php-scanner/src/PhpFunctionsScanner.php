<?php
declare(strict_types = 1);

namespace Gettext\Scanner;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class PhpFunctionsScanner implements FunctionsScannerInterface
{
    protected $parser;
    protected $validFunctions;

    public function __construct(array $validFunctions = null, Parser $parser = null)
    {
        $this->validFunctions = $validFunctions;
        $this->parser = $parser ?: (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    public function scan(string $code, string $filename): array
    {
        $ast = $this->parser->parse($code);

        if (empty($ast)) {
            return [];
        }

        $traverser = new NodeTraverser();
        $visitor = $this->createNodeVisitor($filename);
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->getFunctions();
    }

    protected function createNodeVisitor(string $filename): NodeVisitor
    {
        return new PhpNodeVisitor($filename, $this->validFunctions);
    }
}
