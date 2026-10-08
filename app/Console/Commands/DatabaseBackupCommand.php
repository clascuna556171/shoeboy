<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'shoeboy:backup';

    protected $description = 'Create a snapshot backup of the SQLite database';

    public function handle(BackupService $backups): int
    {
        $path = $backups->create();

        if ($path === null) {
            $this->warn('No SQLite database file found to back up.');

            return self::SUCCESS;
        }

        $this->info('Database snapshot backup created: '.$path);

        return self::SUCCESS;
    }
}
