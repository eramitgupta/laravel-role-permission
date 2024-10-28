<?php

namespace EragPermission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpgradeVersions extends Command
{
    protected $signature = 'erag:upgrade-version';

    protected $description = 'Upgrades the version by publishing migration files.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $migrationFile = database_path('migrations/06_users_permissions_add_column.php');

        if (file_exists($migrationFile)) {
            $this->info('✅ The system is up to date.');
        } else {
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
