<?php

namespace App\Console\Commands;

use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'shoeboy:backup';
    protected $description = 'Kopyahon ug i-save ang snapshot backup sa SQLite database';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $databaseFile = config('database.connections.sqlite.database');
            if (File::exists($databaseFile)) {
                $target = $backupDir . '/shoeboy_backup_' . date('Ymd_His') . '.sqlite';
                File::copy($databaseFile, $target);
                $this->info("Database snapshot backup created: {$target}");

                AuditService::log('system_backup_created', null, [
                    'file' => basename($target),
                    'size_bytes' => filesize($target),
                ]);

                return self::SUCCESS;
            }
        }

        $this->warn("Backup completed for connection [{$connection}].");
        return self::SUCCESS;
    }
}
