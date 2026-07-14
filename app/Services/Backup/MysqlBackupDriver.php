<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MysqlBackupDriver implements BackupDriverInterface
{
    protected string $mysqlDumpPath;
    protected string $mysqlPath;

    public function __construct()
    {
        // Default path jika DB_BACKUP_DUMP_PATH tidak di-set di .env
        $defaultDumpPath = '/usr/bin/mysqldump'; // Default untuk Linux/Ubuntu
        if (PHP_OS_FAMILY === 'Darwin') {
            $defaultDumpPath = '/Applications/XAMPP/xamppfiles/bin/mysqldump'; // Default XAMPP Mac
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $defaultDumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe'; // Default XAMPP Windows
        }

        $this->mysqlDumpPath = env('DB_BACKUP_DUMP_PATH', $defaultDumpPath);
        // Default path jika DB_BACKUP_MYSQL_PATH tidak di-set di .env
        $defaultMysqlPath = '/usr/bin/mysql'; // Default untuk Linux/Ubuntu
        if (PHP_OS_FAMILY === 'Darwin') {
            $defaultMysqlPath = '/Applications/XAMPP/xamppfiles/bin/mysql'; // Default XAMPP Mac
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $defaultMysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe'; // Default XAMPP Windows
        }

        $this->mysqlPath = env('DB_BACKUP_MYSQL_PATH', $defaultMysqlPath);
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
        ];

        // Username harus ada
        $username = env('DB_USERNAME');
        if (!empty($username)) {
            $command[] = '-u' . $username;
        }

        // Password harus tepat setelah username jika ada
        $password = env('DB_PASSWORD');
        if (!empty($password)) {
            $command[] = '-p' . $password;
        }

        // Tambahkan opsi tambahan
        $command[] = '--add-drop-table';

        // Database name
        $database = env('DB_DATABASE');
        if (!empty($database)) {
            $command[] = $database;
        }

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
            Log::error("Restore Error: " . $process->errorOutput());
        }

        return $process->successful();
    }
}
