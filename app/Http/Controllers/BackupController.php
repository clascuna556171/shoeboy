<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(protected BackupService $backups) {}

    public function index(): View
    {
        return view('backups.index', [
            'backups' => $this->backups->all(),
            'isEmpty' => $this->backups->isEmpty(),
            'isFileBased' => $this->backups->isFileBased(),
        ]);
    }

    /** Create a snapshot on the server. */
    public function store(): RedirectResponse
    {
        $path = $this->backups->create();

        return $path
            ? back()->with('success', 'Backup created: '.basename($path))
            : back()->with('error', $this->backups->isFileBased()
                ? 'No database file was found to back up.'
                : 'Could not create a database dump. Ensure pg_dump/mysqldump is available in the runtime.');
    }

    /** Download a snapshot of the live database (export). */
    public function downloadCurrent(): BinaryFileResponse
    {
        if (! $this->backups->isFileBased()) {
            $path = $this->backups->create('export');
            abort_unless($path, 404);

            return response()->download($path, basename($path));
        }

        $database = $this->backups->databasePath();
        abort_unless(file_exists($database), 404);

        return response()->download($database, 'shoeboy-backup-'.now()->format('Ymd_His').'.sqlite');
    }

    /** Download a stored backup. */
    public function download(string $file): BinaryFileResponse
    {
        $path = $this->backups->find($file);
        abort_unless($path, 404);

        return response()->download($path, basename($path));
    }

    /** Replace the database with a stored backup. */
    public function restore(string $file): RedirectResponse
    {
        $path = $this->backups->find($file);
        abort_unless($path, 404);

        if (! $this->backups->isFileBased()) {
            return back()->with('error', 'In-app restore is only supported for SQLite. Restore the SQL dump using your database host (psql, or the platform\'s restore tool).');
        }

        if (! $this->backups->restore($path, basename($path))) {
            return back()->with('error', 'That backup could not be restored — it is not a valid SQLite database.');
        }

        return back()->with('success', 'Database restored from '.basename($path).'.');
    }

    /** Import an uploaded .sqlite file and restore it (works on an empty install too). */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'backup' => ['required', 'file', 'max:51200'],
        ], [
            'backup.required' => 'Choose a .sqlite backup file to import.',
            'backup.max' => 'That backup file is too large (max 50 MB).',
        ]);

        $file = $request->file('backup');

        if (! $this->backups->isFileBased()) {
            return back()->with('error', 'Importing a .sqlite file is only available when the app runs on SQLite.');
        }

        if (! $this->backups->isValidSqlite($file->getRealPath())) {
            return back()->with('error', 'That file is not a valid SQLite database backup.');
        }

        if (! $this->backups->import($file)) {
            return back()->with('error', 'The import failed and the database was left unchanged.');
        }

        return back()->with('success', 'Backup imported — the database has been restored.');
    }

    public function destroy(string $file): RedirectResponse
    {
        abort_unless($this->backups->delete($file), 404);

        return back()->with('undo', [
            'message' => 'Backup '.basename($file).' deleted.',
            'url' => route('backups.undo', basename($file)),
        ]);
    }

    /** Undo a backup deletion (move it back out of the trash). */
    public function undo(string $file): RedirectResponse
    {
        abort_unless($this->backups->restoreTrashed($file), 404);

        return back()->with('success', 'Backup '.basename($file).' restored.');
    }
}
