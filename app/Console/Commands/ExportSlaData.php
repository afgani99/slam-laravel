<?php

namespace App\Console\Commands;

use App\Models\Cid;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ExportSlaData extends Command
{
    protected $signature = 'slam:export {--path=exports : Folder output relatif storage/app}';

    protected $description = 'Export data SLAM ke CSV';

    public function handle(): int
    {
        $folder = trim((string) $this->option('path'), '/');
        $disk = Storage::disk('local');
        File::ensureDirectoryExists($disk->path($folder));

        $timestamp = now()->format('Ymd_His');
        $cidPath = "{$folder}/cids_{$timestamp}.csv";
        $ticketPath = "{$folder}/tickets_{$timestamp}.csv";
        $pendingPath = "{$folder}/pending_intervals_{$timestamp}.csv";

        File::ensureDirectoryExists(dirname($disk->path($cidPath)));
        File::ensureDirectoryExists(dirname($disk->path($ticketPath)));
        File::ensureDirectoryExists(dirname($disk->path($pendingPath)));

        $this->writeCsv($disk->path($cidPath), Cid::query()->orderBy('cid')->get(), [
            'id', 'cid', 'vendor_name', 'customer_name', 'service', 'sla_percentage', 'created_at', 'updated_at',
        ], fn (Cid $cid) => [
            $cid->id,
            $cid->cid,
            $cid->vendor_name,
            $cid->customer_name,
            $cid->service,
            $cid->sla_percentage,
            $cid->created_at,
            $cid->updated_at,
        ]);

        $this->writeCsv($disk->path($ticketPath), Ticket::with('cid')->orderBy('id')->get(), [
            'id', 'ticket_number', 'cid', 'vendor_ticket_number', 'case_type', 'started_at', 'finished_at', 'rfo_action', 'status', 'closed_at', 'created_at', 'updated_at',
        ], fn (Ticket $ticket) => [
            $ticket->id,
            $ticket->ticket_number,
            $ticket->cid?->cid,
            $ticket->vendor_ticket_number,
            $ticket->case_type,
            $ticket->started_at,
            $ticket->finished_at,
            $ticket->rfo_action,
            $ticket->status,
            $ticket->closed_at,
            $ticket->created_at,
            $ticket->updated_at,
        ]);

        $this->writeCsv($disk->path($pendingPath), Ticket::with('pendingIntervals')->orderBy('id')->get(), [
            'ticket_number', 'started_at', 'ended_at', 'note', 'created_at', 'updated_at',
        ], function (Ticket $ticket) {
            return $ticket->pendingIntervals->map(fn ($interval) => [
                $ticket->ticket_number,
                $interval->started_at,
                $interval->ended_at,
                $interval->note,
                $interval->created_at,
                $interval->updated_at,
            ])->all();
        });

        $this->info("Export selesai: {$disk->path($cidPath)}, {$disk->path($ticketPath)}, {$disk->path($pendingPath)}");
        return self::SUCCESS;
    }

    private function writeCsv(string $path, iterable $rows, array $header, callable $rowMapper): void
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, $header);

        foreach ($rows as $row) {
            $mapped = $rowMapper($row);
            if (isset($mapped[0]) && is_array($mapped[0])) {
                foreach ($mapped as $subRow) {
                    fputcsv($handle, $subRow);
                }
                continue;
            }
            fputcsv($handle, $mapped);
        }

        fclose($handle);
    }
}
