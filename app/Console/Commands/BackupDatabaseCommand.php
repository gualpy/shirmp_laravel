<?php

namespace App\Console\Commands;

use App\Support\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:db';

    protected $description = 'Create a database backup in storage/app/backups/database';

    public function handle(BackupService $backupService): int
    {
        try {
            $result = $backupService->backupDatabase();
            $this->info('Database backup created: '.$result['path']);
            Log::info('Database backup created.', $result);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            Log::error('Database backup failed.', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
