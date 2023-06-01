<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-23 08:53:19
 * @modify date 2023-01-23 09:06:47
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Parser
{
    public static function isOption(string $item)
    {
        return substr($item, 0,1) === '-' || substr($item, 0,2) === '--';
    }

    public static function isArgument(string $item)
    {
        return !self::isOption($item);
    }

    public static function setOption(Command $command, string $item)
    {
        $option = explode('|', str_replace('-', '', $item));

        // set name and alias
        $name = trim(explode('=', explode(' : ', $option[1]??$option[0])[0])[0], '?');
        $alias = explode('=', $option[0]??'')[0];

        // set option argument
        $addOptionArguments = [
            $name,
            $alias, 
            // mode optional|required
            (substr($item, -1) === '?' ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_REQUIRED),
            // description
            (stripos($item, ':') ? trim(substr($item, strpos($item, ':')),' : ') : ''),
            // set default
            (stripos($item, '=') ? trim((stripos($item, '=') ? trim(explode(' : ', substr($item, strpos($item, '=')))[0],'=') : null),'=') : null)
        ];

        $command->addOption(...$addOptionArguments);
    }

    public static function setArgument(Command $command, string $item)
    {
        // set name and alias
        $argument = explode('=', $item);

        // set option argument
        $addArgumentArguments = [
            // Name
            trim($argument[0], '?'),
            // mode optional|required
            (substr($item, -1) === '?' || empty($item) ? InputArgument::OPTIONAL : InputArgument::REQUIRED),
            // description
            (stripos($item, ':') ? trim(substr($item, strpos($item, ':')),' : ') : ''),
            // set default
            $argument[1]??null
        ];

        $command->addArgument(...$addArgumentArguments);
    }

    public static function parseSignature(Command $command, string $signature)
    {
        // set argument and option
        $argumentAndOption = preg_split('/(?<=})[\s+.-]+/i', $signature);

        foreach ($argumentAndOption as $item) {
            $item = str_replace(['{','}'], '', $item);

            // option
            if (self::isOption($item)) self::setOption($command, $item);
            // Argument
            else self::setArgument($command, $item);
        }
    }
}