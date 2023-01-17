<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 12:29:51
 * @modify date 2023-01-15 07:36:27
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
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends CoreCommand
{
    use Utils;
    
    /**
     * Command name
     *
     * @var string
     */
    protected string $name = '';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Write your help word with
     * this property
     *
     * @var string
     */
    protected string $help = '';

    /**
     * Command signature is combination of name,
     * argument, and option
     *
     * @var string
     */
    protected string $signature = '';

    /**
     * Input and Output interface property
     *
     * @var [type]
     */
    protected $io = null;
    protected $input = null;
    protected $output = null;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);        
        return $this->handle()??1;
    }

    /**
     * Write some conde inside
     * this method
     *
     * @return void
     */
    abstract protected function handle();

    /**
     * Command configuration, setup your
     * description, name, argument, options etc
     *
     * @return void
     */
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
                    trim($argument[0], '?'),
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