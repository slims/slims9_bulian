<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * Modification from CliDumper by Drajat Hasan <drajathasan20@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace SLiMS\Debug\Dumper;

use Symfony\Component\VarDumper\Cloner\Cursor;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * CliDumper dumps variables for command line output.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Cli extends CliDumper
{
    public static $defaultColors;
    /** @var callable|resource|string|null */
    public static $defaultOutput = 'php://stdout';

    private array $displayOptions = [
        'fileLinkFormat' => null,
    ];

    private bool $handlesHrefGracefully;

    public function __construct($output = null, string $charset = null, int $flags = 0)
    {
        parent::__construct($output, $charset, $flags);
        $this->styles['label'] = '38;5;105';
    }

    /**
     * @return void
     */
    protected function dumpLine(int $depth, bool $endOfValue = false)
    {
        if ($this->colors) {
            $this->line = sprintf("\033[%sm%s\033[m", $this->styles['default'], $this->line);
            // $this->line = str_replace('^', PHP_EOL, $this->line);
        }
        parent::dumpLine($depth);
    }
}
