<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;

class MysqlBackupDriver implements BackupDriverInterface
{
    protected string $mysqlDumpPath;
    protected string $mysqlPath;

    public function __construct()
    {
        $this->mysqlDumpPath = env('DB_BACKUP_DUMP_PATH', '/Applications/XAMPP/xamppfiles/bin/mysqldump');
        $this->mysqlPath = env('DB_BACKUP_MYSQL_PATH', '/Applications/XAMPP/xamppfiles/bin/mysql');
    }

    public function backup(): string
    {
        $fileName = 'backup-' . date('Y-m-d-His') . '.sql';
        $path = storage_path('app/backups/' . $fileName);

        if (!File::isDirectory(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }

        $command = [
            $this->mysqlDumpPath,
            '-u' . env('DB_USERNAME'),
            '--add-drop-table',
        ];

        if (!empty(env('DB_PASSWORD'))) {
            $command[] = '-p' . env('DB_PASSWORD');
        }

        $command[] = env('DB_DATABASE');
        $command[] = '--result-file=' . $path;

        $process = Process::run($command);

        if (!$process->successful()) {
            throw new \Exception("Backup failed: " . $process->errorOutput());
        }

        return $path;
    }

    public function restore(string $filePath): bool
    {
        $command = [
            $this->mysqlPath,
            '-u' . env('DB_USERNAME'),
        ];
        
        if (!empty(env('DB_PASSWORD'))) {
            $command[] = '-p' . env('DB_PASSWORD');
        }
        
        $command[] = env('DB_DATABASE');

        // Gunakan stream file langsung ke stdin untuk memori yang lebih efisien
        $process = Process::input(fopen($filePath, 'r'))->run($command);

        if (!$process->successful()) {
            \Log::error("Restore Error: " . $process->errorOutput());
        }

        return $process->successful();
    }
}
