<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\File;

class SqliteBackupDriver implements BackupDriverInterface
{
    public function backup(): string
    {
        $fileName = 'backup-' . date('Y-m-d-His') . '.sqlite';
        $destination = storage_path('app/backups/' . $fileName);

        if (!File::isDirectory(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }

        File::copy(database_path('database.sqlite'), $destination);

        return $destination;
    }

    public function restore(string $filePath): bool
    {
        return File::copy($filePath, database_path('database.sqlite'));
    }
}
