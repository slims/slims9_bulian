<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 12:29:51
 * @modify date 2023-01-23 11:00:31
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;

abstract class Command extends SymfonyCommand
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

    /**
     * cli Interface property
     */
    protected ?object $terminal =  null;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output); 
        $this->terminal = new Terminal;    
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
        
        // signature processor
        Parser::parseSignature($this, implode(' ', $signature));
    }
}