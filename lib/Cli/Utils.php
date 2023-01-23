<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:15:06
 * @modify date 2023-01-23 12:16:19
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;

trait Utils
{
    public function options()
    {
        $defaultOptions = ['help' => false,'quiet' => false,'verbose' => false,'version' => false,'ansi' => null,'no-interaction' => false];
        return array_values(array_diff_assoc($this->input->getOptions(), $defaultOptions));
    }

    public function option(string $key)
    {
        return $this->input->getOption($key);
    }

    public function arguments()
    {
        return $this->input->getArguments();
    }

    public function argument(string $key)
    {
        return $this->input->getArgument($key);
    }

    public function command(string $commandName, array $arguments)
    {
        $command = $this->getApplication()->find($commandName);
        return $command->run(new ArrayInput($arguments), $this->output);
    }

    public function output(string $content)
    {
        $this->output->writeln($content);
    }

    public function success(string $content)
    {
        $this->output('<info>' . $content . '</info>');
    }

    public function info(string $content)
    {
        $this->output('<fg=cyan>' . $content . '</>');
    }

    public function error(string $content)
    {
        $this->output('<error>' . $content . '</error>');
    }

    public function newLine()
    {
        $this->io->newLine(...func_get_args());
    }

    public function justify(array $word, string $separator = '.')
    {
        $separatorFormatter = str_repeat($separator,($this->terminal->getWidth() - (strlen(strip_tags($word[0])) + strlen(strip_tags($word[1])))));
        $this->output($word[0] . $separatorFormatter . (preg_match('/\<|\>/i', $word[1]) ? $word[1] : '<info>' . $word[1] . '</info>'));
    }

    public function json($content)
    {
        $this->output(json_encode($content, \JSON_PRETTY_PRINT));
    }

    public function table(array $header, array $data)
    {
        $table = new Table($this->output);
        $table
            ->setHeaders($header)
            ->setRows($data);
        $table->render();
    }
}
