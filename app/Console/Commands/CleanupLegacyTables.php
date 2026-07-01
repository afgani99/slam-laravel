<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupLegacyTables extends Command
{
    protected $signature = 'slam:cleanup-legacy';

    protected $description = 'Hapus tabel legacy (cid_data, opened_ticket, closed_ticket) dari database';

    public function handle(): int
    {
        $tables = ['cid_data', 'opened_ticket', 'closed_ticket'];
        $existing = [];

        foreach ($tables as $table) {
            if (DB::select("SHOW TABLES LIKE '{$table}'")) {
                $existing[] = $table;
            }
        }

        if (empty($existing)) {
            $this->info('Tidak ada tabel legacy yang ditemukan. Database sudah bersih.');
            return self::SUCCESS;
        }

        $this->warn('Tabel legacy yang akan dihapus: ' . implode(', ', $existing));
        if (! $this->confirm('Lanjutkan menghapus tabel-tabel ini?', false)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::statement('ALTER TABLE opened_ticket DROP FOREIGN KEY IF EXISTS opened_ticket_cid_foreign');
        DB::statement('ALTER TABLE closed_ticket DROP FOREIGN KEY IF EXISTS closed_ticket_cid_foreign');

        DB::statement('DROP TABLE IF EXISTS closed_ticket');
        DB::statement('DROP TABLE IF EXISTS opened_ticket');
        DB::statement('DROP TABLE IF EXISTS cid_data');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Tabel legacy berhasil dihapus.');
        return self::SUCCESS;
    }
}
