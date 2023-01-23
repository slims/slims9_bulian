<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-23 10:33:38
 * @modify date 2023-01-23 12:27:45
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use SLiMS\DB;
use SLiMS\Table\Schema;

class DatabaseHealth extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'db:health {--a|action=check : available value is check or checkrepair}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'Get database health info';

    /**
     * Table property
     */
    private array $repairTable = [];

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->option('action')) {
            case 'checkrepair':
                $this->check(false);
                $this->repair();
                break;
            
            default:
                $this->check(true);
                break;
        }
    }

    private function check(bool $verbose = true)
    {
        foreach (Schema::tables(['TABLE_NAME']) as $table) {
            $status = Schema::checkTable($table);

            if ($status->Msg_text !== 'OK') $this->repairTable[] = $table;
            if ($verbose) $this->justify([$table, $status->Msg_text !== 'OK' ? '<error>' . $status->Msg_text . '</error>' : $status->Msg_text]);
        }
    }

    private function repair()
    {
        if (!$this->repairTable) {
            $this->info(__('No table to repair. Everything is all right.'));
            return 1;
        }
        
        foreach ($this->repairTable as $table) {
            dump(Schema::repairTable($table));
        }

        $this->success('Success repair ' . count($this->repairTable) . ' table.');
    }
}