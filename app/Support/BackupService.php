<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

final class BackupService
{
    public function dbDirectory(): string
    {
        return (string) config('backup.database_path');
    }

    public function filesDirectory(): string
    {
        return (string) config('backup.files_path');
    }

    /**
     * @return array{database:string,files:string}
     */
    public function ensureDirectories(): array
    {
        File::ensureDirectoryExists($this->dbDirectory());
        File::ensureDirectoryExists($this->filesDirectory());

        return [
            'database' => $this->dbDirectory(),
            'files' => $this->filesDirectory(),
        ];
    }

    /**
     * @return array{path:string,driver:string}
     */
    public function backupDatabase(): array
    {
        $this->ensureDirectories();

        $driver = (string) config('database.default');
        $timestamp = now()->format('Ymd_His');

        return match ($driver) {
            'sqlite' => $this->backupSqlite($timestamp),
            'mysql' => $this->backupMysql($timestamp),
            'pgsql' => $this->backupPgsql($timestamp),
            default => throw new \RuntimeException("Unsupported database driver [{$driver}] for backup."),
        };
    }

    /**
     * @return array{path:string,source:string}
     */
    public function backupFiles(): array
    {
        $this->ensureDirectories();

        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive extension is required for file backups.');
        }

        $source = storage_path('app/public');
        $timestamp = now()->format('Ymd_His');
        $target = $this->filesDirectory()."/files_backup_{$timestamp}.zip";

        File::ensureDirectoryExists(dirname($target));

        $zip = new ZipArchive();
        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create backup zip file.');
        }

        if (File::isDirectory($source)) {
            foreach (File::allFiles($source) as $file) {
                $relative = ltrim(str_replace($source, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                $zip->addFile($file->getPathname(), 'storage_app_public/'.$relative);
            }
        }

        $zip->close();

        return [
            'path' => $target,
            'source' => $source,
        ];
    }

    /**
     * @return array{database:array<int, array<string, mixed>>,files:array<int, array<string, mixed>>}
     */
    public function listBackups(): array
    {
        $this->ensureDirectories();

        return [
            'database' => $this->listDirectory($this->dbDirectory()),
            'files' => $this->listDirectory($this->filesDirectory()),
        ];
    }

    /**
     * @return array{database:?array<string,mixed>,files:?array<string,mixed>}
     */
    public function latestBackups(): array
    {
        $list = $this->listBackups();

        return [
            'database' => $list['database'][0] ?? null,
            'files' => $list['files'][0] ?? null,
        ];
    }

    /**
     * @return array{
     *   database: array{found:int,deleted:int,kept:int,deleted_files:array<int,string>,keep_last:int},
     *   files: array{found:int,deleted:int,kept:int,deleted_files:array<int,string>,keep_last:int}
     * }
     */
    public function pruneBackups(): array
    {
        $this->ensureDirectories();

        return [
            'database' => $this->pruneDirectory(
                $this->dbDirectory(),
                max(0, (int) config('backup.keep_last_database_backups', 14))
            ),
            'files' => $this->pruneDirectory(
                $this->filesDirectory(),
                max(0, (int) config('backup.keep_last_files_backups', 14))
            ),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listDirectory(string $directory): array
    {
        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file): array => [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size_bytes' => $file->getSize(),
                'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{found:int,deleted:int,kept:int,deleted_files:array<int,string>,keep_last:int}
     */
    private function pruneDirectory(string $directory, int $keepLast): array
    {
        $files = $this->listDirectory($directory);
        $toDelete = array_slice($files, $keepLast);
        $deletedFiles = [];

        foreach ($toDelete as $backup) {
            File::delete($backup['path']);
            $deletedFiles[] = $backup['name'];
        }

        return [
            'found' => count($files),
            'deleted' => count($deletedFiles),
            'kept' => max(0, count($files) - count($deletedFiles)),
            'deleted_files' => $deletedFiles,
            'keep_last' => $keepLast,
        ];
    }

    /**
     * @return array{path:string,driver:string}
     */
    private function backupSqlite(string $timestamp): array
    {
        $source = (string) config('database.connections.sqlite.database');
        if ($source === '' || ! File::exists($source)) {
            throw new \RuntimeException('SQLite database file not found for backup.');
        }

        $target = $this->dbDirectory()."/db_backup_{$timestamp}.sqlite";
        File::copy($source, $target);

        return ['path' => $target, 'driver' => 'sqlite'];
    }

    /**
     * @return array{path:string,driver:string}
     */
    private function backupMysql(string $timestamp): array
    {
        $connection = config('database.connections.mysql');
        $target = $this->dbDirectory()."/db_backup_{$timestamp}.sql";

        $password = (string) ($connection['password'] ?? '');
        $command = sprintf(
            'MYSQL_PWD=%s mysqldump --host=%s --port=%s --user=%s %s > %s',
            escapeshellarg($password),
            escapeshellarg((string) $connection['host']),
            escapeshellarg((string) $connection['port']),
            escapeshellarg((string) $connection['username']),
            escapeshellarg((string) $connection['database']),
            escapeshellarg($target),
        );

        $this->runShellBackup($command, 'MySQL dump failed.');

        return ['path' => $target, 'driver' => 'mysql'];
    }

    /**
     * @return array{path:string,driver:string}
     */
    private function backupPgsql(string $timestamp): array
    {
        $connection = config('database.connections.pgsql');
        $target = $this->dbDirectory()."/db_backup_{$timestamp}.sql";

        $password = (string) ($connection['password'] ?? '');
        $command = sprintf(
            'PGPASSWORD=%s pg_dump --host=%s --port=%s --username=%s --dbname=%s --no-password --file=%s',
            escapeshellarg($password),
            escapeshellarg((string) $connection['host']),
            escapeshellarg((string) $connection['port']),
            escapeshellarg((string) $connection['username']),
            escapeshellarg((string) $connection['database']),
            escapeshellarg($target),
        );

        $this->runShellBackup($command, 'PostgreSQL dump failed.');

        return ['path' => $target, 'driver' => 'pgsql'];
    }

    private function runShellBackup(string $command, string $message): void
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($message.' '.$process->getErrorOutput());
        }
    }
}
