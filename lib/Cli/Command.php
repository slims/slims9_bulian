<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 12:29:51
 * @modify date 2023-01-12 23:22:18
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CoreCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Command extends CoreCommand
{
    use Utils;
    
    protected string $name = '';
    protected string $description = '';
    protected string $help = '';
    protected string $signature = '';
    private $input = null;
    private $output = null;
    private $arguments = [];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->handle();        
        return 1;
    }

    protected function handle()
    {
    }

    protected function configure(): void
    {
        $this->setDescription($this->description);
        $this->setHelp($this->help);
     
        // signature processing
        $signature = explode(' ', $this->signature);
        
        // set name
        $this->setName($signature[0]);
        unset($signature[0]);
        
        // set argument and option
        $argumentAndOption = preg_split('/(?<=})[\s+.-]+/i', implode(' ', $signature));

        foreach ($argumentAndOption as $item) {
            $item = str_replace(['{','}'], '', $item);

            // option
            if (substr($item, 0,2) === '--')
            {
                $option = explode('|', str_replace('-', '', $item));

                // set name and alias
                $name = explode('=', explode(' : ', $option[1]??$option[0])[0])[0];
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

                $this->addOption(...$addOptionArguments);
            }
            
            // Argument
            else
            {
                // set name and alias
                $argument = explode('=', $item);

                // set option argument
                $addArgumentArguments = [
                    // Name
                    $argument[0],
                    // mode optional|required
                    (substr($item, -1) === '?' || empty($item) ? InputArgument::OPTIONAL : InputArgument::REQUIRED),
                    // description
                    (stripos($item, ':') ? trim(substr($item, strpos($item, ':')),' : ') : ''),
                    // set default
                    $argument[1]??null
                ];

                $this->addArgument(...$addArgumentArguments);
            }
        }
    }
}