<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Multitenancy\TenantScopeBypass;
use App\Support\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class BackupRestoreBaselineTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/backups'));
        parent::tearDown();
    }

    public function test_backup_commands_are_registered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('backup:db', $commands);
        $this->assertArrayHasKey('backup:files', $commands);
        $this->assertArrayHasKey('backup:run', $commands);
        $this->assertArrayHasKey('backup:list', $commands);
        $this->assertArrayHasKey('backup:prune', $commands);
    }

    public function test_backup_list_command_works(): void
    {
        File::ensureDirectoryExists(storage_path('app/backups/database'));
        File::ensureDirectoryExists(storage_path('app/backups/files'));
        File::put(storage_path('app/backups/database/test-db.sql'), 'db');
        File::put(storage_path('app/backups/files/test-files.zip'), 'zip');

        $this->artisan('backup:list')
            ->expectsOutput('Database backups')
            ->expectsOutputToContain('test-db.sql')
            ->expectsOutput('Files backups')
            ->expectsOutputToContain('test-files.zip')
            ->assertSuccessful();
    }

    public function test_backup_paths_are_non_public(): void
    {
        $service = app(BackupService::class);
        $dirs = $service->ensureDirectories();

        $this->assertStringStartsWith(storage_path('app/backups'), $dirs['database']);
        $this->assertStringStartsWith(storage_path('app/backups'), $dirs['files']);
        $this->assertStringNotContainsString(public_path(), $dirs['database']);
        $this->assertStringNotContainsString(public_path(), $dirs['files']);
    }

    public function test_superadmin_ops_page_loads_with_backup_section(): void
    {
        File::ensureDirectoryExists(storage_path('app/backups/database'));
        File::put(storage_path('app/backups/database/latest.sql'), 'db');

        $this->actingAs($this->superAdmin())
            ->get('/backoffice/admin/ops')
            ->assertOk()
            ->assertSee('Backups')
            ->assertSee('Keep DB')
            ->assertSee('latest.sql');
    }

    public function test_backup_prune_keeps_only_last_n_files(): void
    {
        config()->set('backup.keep_last_database_backups', 2);
        File::ensureDirectoryExists(storage_path('app/backups/database'));

        File::put(storage_path('app/backups/database/old-a.sql'), 'a');
        touch(storage_path('app/backups/database/old-a.sql'), now()->subMinutes(3)->timestamp);
        File::put(storage_path('app/backups/database/old-b.sql'), 'b');
        touch(storage_path('app/backups/database/old-b.sql'), now()->subMinutes(2)->timestamp);
        File::put(storage_path('app/backups/database/new-c.sql'), 'c');
        touch(storage_path('app/backups/database/new-c.sql'), now()->subMinute()->timestamp);

        $this->artisan('backup:prune')
            ->expectsOutput('Database backups')
            ->expectsOutputToContain('found: 3')
            ->assertSuccessful();

        $this->assertFileDoesNotExist(storage_path('app/backups/database/old-a.sql'));
        $this->assertFileExists(storage_path('app/backups/database/old-b.sql'));
        $this->assertFileExists(storage_path('app/backups/database/new-c.sql'));
    }

    public function test_backup_config_defaults_are_present(): void
    {
        $this->assertSame(14, config('backup.keep_last_database_backups'));
        $this->assertSame(14, config('backup.keep_last_files_backups'));
        $this->assertStringContainsString('storage/app/backups/database', config('backup.database_path'));
        $this->assertStringContainsString('storage/app/backups/files', config('backup.files_path'));
    }

    private function superAdmin(): User
    {
        return app(TenantScopeBypass::class)->run(fn (): User => User::withoutGlobalScopes()->create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin-backup@example.test',
            'password' => Hash::make('password123'),
            'role' => UserRole::SUPER_ADMIN->value,
        ]));
    }
}
