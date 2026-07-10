<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy data from old_sla database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting legacy data migration...');

        // 1. Migrate CIDs
        $this->migrateCids();

        // 2. Migrate Tickets
        $this->migrateTickets();

        $this->info('Migration completed successfully.');
    }

    private function migrateCids()
    {
        $this->info('Migrating CIDs...');
        $cids = \DB::connection('mysql_old')->table('cid_data')->get();

        foreach ($cids as $row) {
            \App\Models\Cid::updateOrCreate(
                ['cid' => $row->cid],
                [
                    'vendor_name' => $row->nama_vendor,
                    'customer_name' => $row->nama_pelanggan,
                    'service' => $row->service,
                    'sla_percentage' => $row->sla_persen,
                    'created_at' => $row->created_at,
                ]
            );
        }
        $this->info('CIDs migrated.');
    }

    private function migrateTickets()
    {
        $this->info('Migrating Tickets...');
        // Migrate Opened
        $opened = \DB::connection('mysql_old')->table('opened_ticket')->get();
        foreach ($opened as $row) {
            $cid = \App\Models\Cid::where('cid', $row->cid)->first();
            if ($cid) {
                // Bersihkan datetime jika '0000-00-00'
                $finished_at = ($row->waktu_selesai === '0000-00-00 00:00:00' || strtotime($row->waktu_selesai) <= 0) ? null : $row->waktu_selesai;
                
                \App\Models\Ticket::updateOrCreate(
                    ['ticket_number' => $row->ticket_id],
                    [
                        'cid_id' => $cid->id,
                        'vendor_ticket_number' => $row->ticket_id_vendor,
                        'case_type' => $row->kasus,
                        'started_at' => $row->waktu_mulai,
                        'finished_at' => $finished_at,
                        'rfo_action' => $row->rfo_action,
                        'status' => 'open',
                        'created_at' => $row->created_at,
                    ]
                );
            }
        }

        // Migrate Closed
        $closed = \DB::connection('mysql_old')->table('closed_ticket')->get();
        foreach ($closed as $row) {
            $cid = \App\Models\Cid::where('cid', $row->cid)->first();
            if ($cid) {
                $finished_at = ($row->waktu_selesai === '0000-00-00 00:00:00' || strtotime($row->waktu_selesai) <= 0) ? null : $row->waktu_selesai;

                \App\Models\Ticket::updateOrCreate(
                    ['ticket_number' => $row->ticket_id],
                    [
                        'cid_id' => $cid->id,
                        'vendor_ticket_number' => $row->ticket_id_vendor,
                        'case_type' => $row->kasus,
                        'started_at' => $row->waktu_mulai,
                        'finished_at' => $finished_at,
                        'rfo_action' => $row->rfo_action,
                        'status' => 'closed',
                        'closed_at' => $finished_at,
                        'created_at' => $row->created_at,
                    ]
                );
            }
        }
        $this->info('Tickets migrated.');
    }
}
