<?php

namespace App\Console\Commands;

use App\Support\BackupService;
use Illuminate\Console\Command;

class BackupListCommand extends Command
{
    protected $signature = 'backup:list';

    protected $description = 'List available database and file backups';

    public function handle(BackupService $backupService): int
    {
        $backups = $backupService->listBackups();

        $this->info('Database backups');
        if ($backups['database'] === []) {
            $this->line('  none');
        } else {
            foreach ($backups['database'] as $backup) {
                $this->line(sprintf('  %s | %s | %s bytes', $backup['modified_at'], $backup['name'], $backup['size_bytes']));
            }
        }

        $this->newLine();
        $this->info('Files backups');
        if ($backups['files'] === []) {
            $this->line('  none');
        } else {
            foreach ($backups['files'] as $backup) {
                $this->line(sprintf('  %s | %s | %s bytes', $backup['modified_at'], $backup['name'], $backup['size_bytes']));
            }
        }

        return self::SUCCESS;
    }
}
