<?php

namespace EragPermission\Commands;

use Illuminate\Console\Command;

class PublishPermissionMigrations extends Command
{
    protected $signature = 'erag:publish-permission';

    protected $description = 'Publish the Permission and Role migration files and models';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {   
        // Step 1: Publish the models
        $this->info('Publishing models...');
        $this->call('vendor:publish', [
            '--tag' => 'erag:publish-permission-models',
            '--force' => true,
        ]);

        // Step 2: Publish the migrations
        $this->info('Publishing migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'erag:publish-permission-migrations',
            '--force' => true,
        ]);

        // Step 3: Run the migrations
        $this->info('Running migrations...');
        $exitCode = $this->call('migrate', ['--force' => true]);


        if ($exitCode === 0) {
            $this->info('Migrations completed successfully.');
        } else {
            $this->error('Migration process encountered errors.');
        }

        // Step 4: Publish the seeder
        $this->info('Publishing seeder...');
        $this->call('vendor:publish', [
            '--tag' => 'erag:publish-permission-role-seeder',
            '--force' => true,
        ]);

        // Step 5: Run the seeder
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
            $this->error('Error running seeder: ' . $e->getMessage());
        }



    }
}
