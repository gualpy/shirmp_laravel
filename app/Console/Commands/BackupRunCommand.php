<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Run database and files backup together';

    public function handle(): int
    {
        $dbStatus = $this->call('backup:db');
        $filesStatus = $this->call('backup:files');

        if ($dbStatus !== self::SUCCESS || $filesStatus !== self::SUCCESS) {
            $this->error('Backup run completed with errors.');

            return self::FAILURE;
        }

        $pruneStatus = $this->call('backup:prune');

        if ($pruneStatus !== self::SUCCESS) {
            $this->error('Backup run finished, but prune failed.');

            return self::FAILURE;
        }

        $this->info('Backup run completed successfully.');

        return self::SUCCESS;
    }
}
