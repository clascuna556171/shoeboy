<?php

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    use RefreshDatabase;

    private array $created = [];

    protected function tearDown(): void
    {
        foreach ($this->created as $path) {
            File::delete($path);
        }
        parent::tearDown();
    }

    public function test_detects_valid_and_invalid_sqlite_files(): void
    {
        $service = app(BackupService::class);

        $valid = $this->makeSqliteFile();
        $this->assertTrue($service->isValidSqlite($valid));

        $junk = $service->directory().DIRECTORY_SEPARATOR.'junk_'.uniqid().'.sqlite';
        File::put($junk, 'this is not a database at all');
        $this->created[] = $junk;
        $this->assertFalse($service->isValidSqlite($junk));

        $this->assertFalse($service->isValidSqlite($service->directory().DIRECTORY_SEPARATOR.'missing_'.uniqid().'.sqlite'));
    }

    public function test_delete_and_undo_move_a_backup_to_and_from_trash(): void
    {
        $service = app(BackupService::class);
        $path = $this->makeSqliteFile();
        $name = basename($path);

        $this->assertNotNull($service->find($name));

        $this->assertTrue($service->delete($name));
        $this->assertNull($service->find($name)); // moved out of the visible list

        $this->assertTrue($service->restoreTrashed($name));
        $this->assertNotNull($service->find($name));
    }

    public function test_find_is_traversal_safe(): void
    {
        $service = app(BackupService::class);

        $this->assertNull($service->find('../../config/database.php'));
        $this->assertNull($service->find('..\\..\\..\\windows\\win.ini'));
    }

    public function test_all_lists_existing_backups(): void
    {
        $service = app(BackupService::class);
        $path = $this->makeSqliteFile();

        $names = array_column($service->all(), 'name');
        $this->assertContains(basename($path), $names);
    }

    private function makeSqliteFile(): string
    {
        $path = app(BackupService::class)->directory().DIRECTORY_SEPARATOR.'test_'.uniqid().'.sqlite';
        $this->created[] = $path;

        $pdo = new \PDO('sqlite:'.$path);
        $pdo->exec('CREATE TABLE sample (id INTEGER PRIMARY KEY, label TEXT)');
        $pdo->exec("INSERT INTO sample (label) VALUES ('x')");
        $pdo = null;

        return $path;
    }
}
