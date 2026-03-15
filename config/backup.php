<?php

return [
    'database_path' => env('BACKUP_DATABASE_PATH', storage_path('app/backups/database')),
    'files_path' => env('BACKUP_FILES_PATH', storage_path('app/backups/files')),
    'keep_last_database_backups' => (int) env('BACKUP_KEEP_DB', 14),
    'keep_last_files_backups' => (int) env('BACKUP_KEEP_FILES', 14),
];
