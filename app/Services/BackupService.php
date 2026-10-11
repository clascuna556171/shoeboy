<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

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

    /** Absolute path to the active database file (SQLite only). */
    public function databasePath(): string
    {
        $connection = config('database.default');

        return (string) (config("database.connections.{$connection}.database") ?: database_path('database.sqlite'));
    }

    /** Active database driver (sqlite, pgsql, mysql, ...). */
    public function driver(): string
    {
        return (string) config('database.default');
    }

    /** Whether backups are a straight copy of a local database file. */
    public function isFileBased(): bool
    {
        return $this->driver() === 'sqlite';
    }

    /** Stored backup file extensions for the active driver. */
    protected function extensions(): array
    {
        return $this->isFileBased() ? ['*.sqlite'] : ['*.sql', '*.dump'];
    }

    /** Connection string / URL used to hand off to a dump utility. */
    protected function connectionUrl(): ?string
    {
        $conn = config('database.connections.'.$this->driver(), []);

        if (! empty($conn['url'])) {
            return $conn['url'];
        }

        $host = $conn['host'] ?? '127.0.0.1';
        $port = $conn['port'] ?? ($this->driver() === 'pgsql' ? 5432 : 3306);
        $database = $conn['database'] ?? '';
        $user = $conn['username'] ?? '';
        $pass = $conn['password'] ?? '';
        $scheme = $this->driver() === 'pgsql' ? 'postgresql' : 'mysql';

        if ($database === '' || $host === '') {
            return null;
        }

        return sprintf('%s://%s:%s@%s:%s/%s', $scheme, urlencode((string) $user), urlencode((string) $pass), $host, $port, $database);
    }

    /** Whether a required CLI utility is callable. */
    protected function binaryAvailable(string $binary): bool
    {
        try {
            return Process::timeout(5)->run([$binary, '--version'])->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Whether any business records exist yet (used to nudge an import). */
    public function isEmpty(): bool
    {
        return Order::query()->count() === 0
            && Item::query()->count() === 0
            && Batch::query()->count() === 0;
    }

    /** Create a snapshot of the current database. Returns the path or null. */
    public function create(?string $prefix = null): ?string
    {
        return $this->isFileBased()
            ? $this->createFromFile($prefix)
            : $this->createFromDump($prefix);
    }

    /** SQLite: a straight copy of the database file. */
    protected function createFromFile(?string $prefix): ?string
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
     * PostgreSQL/MySQL: shell out to pg_dump/mysqldump. Returns null when the
     * utility is not installed in the runtime (e.g. a slim container) so the UI
     * can report gracefully rather than pretending a backup happened.
     */
    protected function createFromDump(?string $prefix): ?string
    {
        $binary = $this->driver() === 'pgsql' ? 'pg_dump' : 'mysqldump';
        $url = $this->connectionUrl();

        if (! $url) {
            return null;
        }

        if (! $this->binaryAvailable($binary)) {
            Log::warning('Database backup skipped: dump utility not available.', [
                'binary' => $binary,
                'driver' => $this->driver(),
            ]);

            return null;
        }

        $stamp = now()->format('Ymd_His');
        $name = 'shoeboy_backup_'.$stamp.($prefix ? "_{$prefix}" : '').'.sql';
        $target = $this->directory().DIRECTORY_SEPARATOR.$name;

        try {
            $command = $this->driver() === 'pgsql'
                ? [$binary, '--dbname='.$url, '--file='.$target, '--no-owner', '--no-privileges']
                : [$binary, '--result-file='.$target, $url];

            $result = Process::timeout(180)->run($command);

            if (! $result->successful() || ! File::exists($target) || filesize($target) === 0) {
                Log::error('Database dump failed.', [
                    'binary' => $binary,
                    'exit_code' => $result->exitCode(),
                    'error' => trim($result->errorOutput()),
                    'server_version' => $this->serverVersion(),
                ]);

                File::delete($target);

                return null;
            }
        } catch (\Throwable $e) {
            Log::error('Database dump threw an exception.', [
                'binary' => $binary,
                'exception' => $e->getMessage(),
            ]);

            File::delete($target);

            return null;
        }

        AuditService::log('system_backup_created', null, [
            'file' => $name,
            'size_bytes' => filesize($target),
        ]);

        $this->prune();

        return $target;
    }

    /** Best-effort server version string, used only for failure diagnostics. */
    protected function serverVersion(): ?string
    {
        try {
            return (string) DB::selectOne('select version() as v')->v;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * All backups, newest first.
     *
     * @return array<int, array{name: string, path: string, size: int, modified: int}>
     */
    public function all(): array
    {
        $files = [];
        foreach ($this->extensions() as $pattern) {
            $files = array_merge($files, File::glob($this->directory().DIRECTORY_SEPARATOR.$pattern) ?: []);
        }

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
        if (! $this->isFileBased()) {
            // Restoring a SQL dump in-process is destructive and platform
            // specific; cloud restores go through the host's database tooling.
            return false;
        }

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
        $files = [];
        foreach ($this->extensions() as $pattern) {
            $files = array_merge($files, File::glob($dir.DIRECTORY_SEPARATOR.$pattern) ?: []);
        }

        if (count($files) <= $keep) {
            return;
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, $keep) as $old) {
            File::delete($old);
        }
    }
}
