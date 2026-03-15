<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Support\BackupService;
use App\Support\ReadinessService;

final class BackofficeOpsService
{
    public function __construct(
        private readonly ReadinessService $readinessService,
        private readonly BackupService $backupService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $readiness = $this->readinessService->check();

        return [
            'app_env' => config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'queue_connection' => config('queue.default'),
            'cache_store' => config('cache.default'),
            'db_driver' => config('database.default'),
            'readiness' => $readiness,
            'backups' => $this->backupService->latestBackups(),
            'backup_counts' => [
                'database' => count($this->backupService->listBackups()['database']),
                'files' => count($this->backupService->listBackups()['files']),
            ],
            'backup_policy' => [
                'database' => (int) config('backup.keep_last_database_backups', 14),
                'files' => (int) config('backup.keep_last_files_backups', 14),
            ],
            'docs' => [
                ['label' => 'Production Env', 'href' => '/docs/deployment/production_env.md'],
                ['label' => 'Logging', 'href' => '/docs/deployment/logging.md'],
                ['label' => 'Queues & Scheduler', 'href' => '/docs/deployment/queues_and_scheduler.md'],
                ['label' => 'Optimize Commands', 'href' => '/docs/deployment/optimize_commands.md'],
                ['label' => 'Filesystem & Permissions', 'href' => '/docs/deployment/filesystem_and_permissions.md'],
                ['label' => 'Backup & Restore', 'href' => '/docs/deployment/backup_restore_baseline.md'],
                ['label' => 'Deployment Checklist', 'href' => '/docs/deployment/deployment_checklist.md'],
            ],
        ];
    }
}
