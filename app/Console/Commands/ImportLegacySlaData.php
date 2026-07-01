<?php

namespace App\Console\Commands;

use App\Models\Cid;
use App\Models\Ticket;
use App\Models\TicketPendingInterval;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportLegacySlaData extends Command
{
    protected $signature = 'slam:import-legacy {--file=sla_db.sql : Path ke SQL dump legacy} {--truncate : Hapus data baru dulu sebelum import}';

    protected $description = 'Import data legacy SLAM dari file SQL dump ke struktur Laravel baru';

    public function handle(): int
    {
        $file = (string) $this->option('file');
        $path = base_path($file);

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('ticket_pending_intervals')->truncate();
            DB::table('tickets')->truncate();
            DB::table('cids')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            $this->error('Gagal membaca file SQL.');
            return self::FAILURE;
        }

        $legacyCidRows = $this->parseInsertRows($sql, 'cid_data');
        $legacyOpenRows = $this->parseInsertRows($sql, 'opened_ticket');
        $legacyClosedRows = $this->parseInsertRows($sql, 'closed_ticket');

        $cidMap = [];

        DB::transaction(function () use (&$cidMap, $legacyCidRows, $legacyOpenRows, $legacyClosedRows): void {
            foreach ($legacyCidRows as $row) {
                $cid = Cid::updateOrCreate(
                    ['cid' => $row['cid']],
                    [
                        'vendor_name' => $row['nama_vendor'],
                        'customer_name' => $row['nama_pelanggan'],
                        'service' => $row['service'],
                        'sla_percentage' => $row['sla_persen'] ?? 99.00,
                    ]
                );

                $cidMap[$row['cid']] = $cid->id;
            }

            $this->importTickets($legacyOpenRows, $cidMap, false);
            $this->importTickets($legacyClosedRows, $cidMap, true);
        });

        $this->info(sprintf('Import selesai. CID: %d, Open: %d, Closed: %d', count($legacyCidRows), count($legacyOpenRows), count($legacyClosedRows)));
        return self::SUCCESS;
    }

    private function importTickets(array $rows, array $cidMap, bool $closed): void
    {
        foreach ($rows as $row) {
            if (empty($row['cid']) || ! isset($cidMap[$row['cid']])) {
                continue;
            }

            $status = $closed ? Ticket::STATUS_CLOSED : (($row['status'] ?? '') === 'pending' ? Ticket::STATUS_PENDING : Ticket::STATUS_OPEN);

            $ticket = Ticket::updateOrCreate(
                ['ticket_number' => $row['ticket_id'] ?: 'legacy-' . $row['id']],
                [
                    'cid_id' => $cidMap[$row['cid']],
                    'vendor_ticket_number' => $row['ticket_id_vendor'] !== '-' ? $row['ticket_id_vendor'] : null,
                    'case_type' => $row['kasus'] ?: Ticket::CASE_LINK_DOWN,
                    'started_at' => $this->parseDateTime($row['waktu_mulai']),
                    'finished_at' => $this->parseDateTime($row['waktu_selesai']),
                    'rfo_action' => $this->nullIfDash($row['rfo_action'] ?? null),
                    'status' => $status,
                    'closed_at' => $closed ? $this->parseDateTime($row['waktu_selesai']) ?? now() : null,
                ]
            );

            if ($closed) {
                $this->syncPendingIntervals($ticket, $row);
            }
        }
    }

    private function syncPendingIntervals(Ticket $ticket, array $row): void
    {
        $start = $this->parseDateTime($row['pending_start'] ?? null);
        $end = $this->parseDateTime($row['pending_end'] ?? null);

        if (! $start || ! $end) {
            return;
        }

        TicketPendingInterval::updateOrCreate(
            [
                'ticket_id' => $ticket->id,
                'started_at' => $start,
                'ended_at' => $end,
            ],
            [
                'note' => 'Imported from legacy dump',
            ]
        );
    }

    private function parseInsertRows(string $sql, string $table): array
    {
        if (! preg_match('/INSERT INTO `' . preg_quote($table, '/') . '` .*? VALUES\n(.*?);/s', $sql, $matches)) {
            return [];
        }

        $rows = [];
        $values = trim($matches[1]);
        preg_match_all('/\((.*?)\)(?:,\n|,\s*\n|\n|$)/s', $values, $rowMatches);

        foreach ($rowMatches[1] as $row) {
            $rows[] = $this->parseSqlTuple($row);
        }

        return $rows;
    }

    private function parseSqlTuple(string $row): array
    {
        $tokens = str_getcsv($row, ',', "'", "\\");

        $columns = match (count($tokens)) {
            7 => ['id', 'cid', 'nama_vendor', 'nama_pelanggan', 'service', 'sla_persen', 'created_at'],
            14 => ['id', 'cid', 'nama_vendor', 'nama_pelanggan', 'service', 'ticket_id_vendor', 'kasus', 'waktu_mulai', 'waktu_selesai', 'rfo_action', 'created_at', 'ticket_id', 'status', 'pending_start', 'pending_end'],
            default => ['id', 'cid', 'nama_vendor', 'nama_pelanggan', 'service', 'ticket_id_vendor', 'kasus', 'waktu_mulai', 'waktu_selesai', 'rfo_action', 'created_at', 'ticket_id', 'pending_start', 'pending_end'],
        };

        $data = [];
        foreach ($columns as $index => $column) {
            $data[$column] = $this->normalizeSqlValue($tokens[$index] ?? null);
        }

        return $data;
    }

    private function normalizeSqlValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || strtoupper($value) === 'NULL') {
            return null;
        }

        return stripslashes($value);
    }

    private function parseDateTime(mixed $value): ?Carbon
    {
        $value = $this->normalizeSqlValue($value);
        if (! $value || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function nullIfDash(mixed $value): ?string
    {
        $value = $this->normalizeSqlValue($value);
        return $value === '-' ? null : $value;
    }
}
