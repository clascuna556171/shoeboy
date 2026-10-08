<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Local, file-based backup & restore for the SQLite database.
 *
 * Backups are plain copies of the database file stored in storage/app/backups.
 * A snapshot of the current database is always taken before any restore/import
 * so a bad restore can itself be rolled back.
 */
class BackupService
{
    public const MAX_BACKUPS = 30;

    /** Absolute path to the backups directory (created on demand). */
    public function directory(): string
    {
        $dir = storage_path('app/backups');

        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    /** Absolute path to the active database file. */
    public function databasePath(): string
    {
        $connection = config('database.default');

        return (string) (config("database.connections.{$connection}.database") ?: database_path('database.sqlite'));
    }

    /** Whether any business records exist yet (used to nudge an import). */
    public function isEmpty(): bool
    {
        return Order::query()->count() === 0
            && Item::query()->count() === 0
            && Batch::query()->count() === 0;
    }

    /** Create a snapshot copy of the current database. Returns the path or null. */
    public function create(?string $prefix = null): ?string
    {
        $database = $this->databasePath();

        if (! File::exists($database)) {
            return null;
        }

        $stamp = now()->format('Ymd_His');
        $name = 'shoeboy_backup_'.$stamp.($prefix ? "_{$prefix}" : '').'.sqlite';
        $target = $this->directory().DIRECTORY_SEPARATOR.$name;

        File::copy($database, $target);

        AuditService::log('system_backup_created', null, [
            'file' => $name,
            'size_bytes' => filesize($target),
        ]);

        $this->prune();

        return $target;
    }

    /**
     * All backups, newest first.
     *
     * @return array<int, array{name: string, path: string, size: int, modified: int}>
     */
    public function all(): array
    {
        $files = File::glob($this->directory().DIRECTORY_SEPARATOR.'*.sqlite') ?: [];

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn ($path) => [
            'name' => basename($path),
            'path' => $path,
            'size' => filesize($path),
            'modified' => filemtime($path),
        ], $files);
    }

    /** Resolve a stored backup by filename (traversal-safe). */
    public function find(string $name): ?string
    {
        $name = basename($name);
        $path = $this->directory().DIRECTORY_SEPARATOR.$name;

        return File::exists($path) ? $path : null;
    }

    /** Where "deleted" backups rest until pruned, so a delete can be undone. */
    public function trashDirectory(): string
    {
        $dir = $this->directory().DIRECTORY_SEPARATOR.'.trash';

        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    /** Soft-delete a backup by moving it to the trash (recoverable via restoreTrashed). */
    public function delete(string $name): bool
    {
        $path = $this->find($name);

        if (! $path) {
            return false;
        }

        return File::move($path, $this->trashDirectory().DIRECTORY_SEPARATOR.basename($name)) !== false;
    }

    /** Bring a trashed backup back into the visible list. */
    public function restoreTrashed(string $name): bool
    {
        $name = basename($name);
        $trashed = $this->trashDirectory().DIRECTORY_SEPARATOR.$name;

        if (! File::exists($trashed)) {
            return false;
        }

        return File::move($trashed, $this->directory().DIRECTORY_SEPARATOR.$name) !== false;
    }

    /**
     * Replace the active database with a backup, snapshotting the current one first.
     *
     * Validates the source SQLite file, disconnects the live connection so the
     * file can be replaced atomically, and rolls back to the pre-restore snapshot
     * if anything goes wrong.
     */
    public function restore(string $sourcePath, string $auditName): bool
    {
        if (! $this->isValidSqlite($sourcePath)) {
            return false;
        }

        $database = $this->databasePath();
        $connection = config('database.default');

        // Release the live connection so the file handle is free to swap.
        DB::purge($connection);

        // Safety net: capture the state we are about to overwrite.
        $snapshot = $this->create('pre-restore');

        $temp = $database.'.restore-'.uniqid('', true);

        try {
            File::copy($sourcePath, $temp);

            // Atomic replace (same directory); fall back to a direct copy.
            if (! @rename($temp, $database)) {
                File::copy($temp, $database);
                File::delete($temp);
            }
        } catch (\Throwable $e) {
            File::delete($temp);

            if ($snapshot && File::exists($snapshot)) {
                File::copy($snapshot, $database);
            }

            DB::reconnect($connection);

            return false;
        }

        DB::reconnect($connection);

        AuditService::log('system_backup_restored', null, ['file' => $auditName]);

        return true;
    }

    /** Whether a file is a structurally valid SQLite database. */
    public function isValidSqlite(string $path): bool
    {
        if (! File::exists($path) || filesize($path) < 512) {
            return false;
        }

        $handle = @fopen($path, 'rb');
        if (! $handle) {
            return false;
        }
        $header = (string) fread($handle, 16);
        fclose($handle);

        if (strncmp($header, 'SQLite format 3', 15) !== 0) {
            return false;
        }

        try {
            $pdo = new \PDO('sqlite:'.$path);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();
            $pdo = null;

            return $result === 'ok';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Validate and restore an uploaded .sqlite file. */
    public function import(UploadedFile $file): bool
    {
        $path = $file->getRealPath();

        return $this->restore($path, $file->getClientOriginalName());
    }

    /** Keep only the most recent backups (and trashed backups). */
    public function prune(int $keep = self::MAX_BACKUPS): void
    {
        $this->pruneDir($this->directory(), $keep);
        $this->pruneDir($this->trashDirectory(), $keep);
    }

    protected function pruneDir(string $dir, int $keep): void
    {
        $files = File::glob($dir.DIRECTORY_SEPARATOR.'*.sqlite') ?: [];

        if (count($files) <= $keep) {
            return;
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, $keep) as $old) {
            File::delete($old);
        }
    }
}
