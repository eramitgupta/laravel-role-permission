<?php

namespace EragPermission\Commands;

use Illuminate\Console\Command;

class NewUpdate extends Command
{
    protected $signature = 'erag:new-update';

    protected $description = 'Migrates existing new migration files and publish files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // Step 1: Publish the migrations
        $this->info('Publishing migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'erag:new-update',
            '--force' => true,
        ]);

        $this->info('Running migrations...');
        $exitCode = $this->call('migrate', ['--force' => true]);

        if ($exitCode === 0) {
            $this->info('Migrations completed successfully.');
        } else {
            $this->error('Migration process encountered errors.');
        }
    }
}
