<?php

namespace EragPermission\Commands;

namespace EragPermission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class UpgradeVersions extends Command
{
    protected $signature = 'erag:upgrade-version';

    protected $description = 'Upgrades the version by publishing migration files and updating the package.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Starting the upgrade process for the Erag Laravel Role Permission package...');

        $this->updatePackage();

        $this->publishMigrations();

        $this->info('Upgrade process completed.');
    }

    protected function updatePackage(): void
    {
        $this->info('Updating the erag/laravel-role-permission package...');

        $process = new Process(['composer', 'update', 'erag/laravel-role-permission']);

        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Failed to update the package. Please check your Composer configuration.');
        } else {
            $this->info('Package updated successfully.');
        }
    }

    protected function publishMigrations(): void
    {
        $migrationFile = database_path('migrations/06_users_permissions_add_column.php');

        if (file_exists($migrationFile)) {
            $this->info('✅ The system is up to date.');
        } else {
            $this->info('Publishing migration files...');

            $this->info('🔄 Upgrading the version by publishing new migration files...');

            $stubPath = __DIR__.'/../database/06_users_permissions_add_column.php.stub';

            File::copy($stubPath, $migrationFile);

            $this->info('🎉 Migration file published successfully. Upgrade complete.');

            $this->info('Running migrations...');

            $exitCode = $this->call('migrate', ['--force' => true]);

            if ($exitCode === 0) {
                $this->info('Migrations completed successfully.');
            } else {
                $this->error('Migration process encountered errors.');
            }
        }
    }
}
