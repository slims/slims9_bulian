<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * Modification from HtmlDumper by Drajat Hasan <drajathasan20@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SLiMS\Debug\Dumper;

use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * CliDumper dumps variables for command line output.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Web extends HtmlDumper
{
    public static $defaultOutput = 'php://output';

    private $displayOptions = [
        'maxDepth' => 1,
        'maxStringLength' => 160,
        'fileLinkFormat' => null,
    ];

    private $extraDisplayOptions = [];

    protected $styles;

    public function __construct($output = null, string $charset = null, int $flags = 0)
    {
        HtmlDumper::__construct($output, $charset, $flags);
        $this->styles['label'] = 'color:#d0d0d0;display:block';
    }
}