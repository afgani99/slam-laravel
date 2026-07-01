<?php

namespace Tests\Feature;

use App\Models\Cid;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LegacyImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_command_creates_csv_files(): void
    {
        Cid::factory()->count(2)->create();
        Ticket::factory()->count(2)->create();

        Artisan::call('slam:export', ['--path' => 'exports-test']);

        $files = File::files(storage_path('app/private/exports-test'));
        $this->assertNotEmpty($files);
    }

    public function test_import_command_reads_legacy_sql_dump(): void
    {
        Artisan::call('slam:import-legacy', ['--file' => 'sla_db.sql', '--truncate' => true]);

        $this->assertGreaterThan(0, Cid::count());
        $this->assertGreaterThan(0, Ticket::count());
    }
}
