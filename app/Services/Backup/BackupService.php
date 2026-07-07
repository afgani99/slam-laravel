<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Config;

class BackupService
{
    public function getDriver(): BackupDriverInterface
    {
        $connection = Config::get('database.default');

        return match ($connection) {
            'mysql' => new MysqlBackupDriver(),
            'sqlite' => new SqliteBackupDriver(),
            default => throw new \Exception("Unsupported database connection: {$connection}"),
        };
    }
}
