<?php

namespace App\Console\Commands;

use App\Support\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupPruneCommand extends Command
{
    protected $signature = 'backup:prune';

    protected $description = 'Prune old database and file backups according to retention policy';

    public function handle(BackupService $backupService): int
    {
        try {
            $result = $backupService->pruneBackups();

            $this->info('Database backups');
            $this->line(sprintf(
                '  found: %d | deleted: %d | kept: %d | policy keep_last=%d',
                $result['database']['found'],
                $result['database']['deleted'],
                $result['database']['kept'],
                $result['database']['keep_last'],
            ));

            $this->info('Files backups');
            $this->line(sprintf(
                '  found: %d | deleted: %d | kept: %d | policy keep_last=%d',
                $result['files']['found'],
                $result['files']['deleted'],
                $result['files']['kept'],
                $result['files']['keep_last'],
            ));

            Log::info('Backup prune completed.', $result);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            Log::error('Backup prune failed.', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
