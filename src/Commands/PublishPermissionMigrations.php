<?php

namespace EragPermission\Commands;

use Illuminate\Console\Command;

class PublishPermissionMigrations extends Command
{
    protected $signature = 'erag:publish-permission {--migrate} {--seed}';

    protected $description = 'Publish the Permission and Role migration files and models';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {

        $this->info('Publishing migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'erag:publish-permission-migrations',
            '--force' => true,
        ]);

        if ($this->option('migrate')) {
            $this->info('Running migrations...');
            $exitCode = $this->call('migrate', ['--force' => true]);

            if ($exitCode === 0) {
                $this->info('Migrations completed successfully.');
            } else {
                $this->error('Migration process encountered errors.');
            }
        }

        $this->info('Publishing seeder...');
        $this->call('vendor:publish', [
            '--tag' => 'erag:publish-permission-role-seeder',
            '--force' => true,
        ]);

        if ($this->option('seed')) {
            $this->info('Running seeder...');
            try {
                $exitCode = $this->call('db:seed', [
                    '--class' => 'RolePermissionSeeder',
                    '--force' => true,
                ]);

                if ($exitCode === 0) {
                    $this->info('Seeder completed successfully.');
                } else {
                    $this->error('Seeder process encountered errors.');
                }
            } catch (\Exception $e) {
                $this->error('Error running seeder: '.$e->getMessage());
            }
        }
    }
}
