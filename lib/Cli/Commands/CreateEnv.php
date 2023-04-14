<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-04-14 10:16:43
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

class CreateEnv extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'env {--M|mode=production : development = all error will be appear, production = silent error} {--R|rewrite=no}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'create or recreate env.php file';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $envSample = SB . 'config/env.sample.php';
        $envFile = str_replace('.sample', '', $envSample);

        if (!file_exists($envSample)) {
            $this->error("$envSample is not exists!");
            return 0;
        }

        if (file_exists($envFile) && $this->option('rewrite') == 'no') {
            $this->info("$envFile is still exists. If you want to dump it please use option --rewrite=yes");
            return;
        }

        $sample = file_get_contents($envSample);
        $sample = str_replace('<environment>', $this->option('mode'), $sample);
        $sample = str_replace('<conditional_environment>', $this->option('mode'), $sample);
        $sample = str_replace('\'<based_on_ip>\'', 'false', $sample);
        $sample = str_replace('<ip_range>', '', $sample);

        $write = file_put_contents($envFile, $sample);

        if ($write === false) {
            $this->error("$envFile is failed to write!");
            return 0;
        }

        $this->success('Success write env.php');
        return 1;
    }
}