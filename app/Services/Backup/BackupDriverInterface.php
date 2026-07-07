<?php

namespace App\Services\Backup;

interface BackupDriverInterface
{
    public function backup(): string; // Returns the local path to the backup file
    public function restore(string $filePath): bool;
}
