<?php

namespace App\Console\Commands;

use App\Support\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupFilesCommand extends Command
{
    protected $signature = 'backup:files';

    protected $description = 'Create a files backup in storage/app/backups/files';

    public function handle(BackupService $backupService): int
    {
        try {
            $result = $backupService->backupFiles();
            $this->info('Files backup created: '.$result['path']);
            Log::info('Files backup created.', $result);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            Log::error('Files backup failed.', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
