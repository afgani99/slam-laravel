<?php

namespace Tests\Feature;

use App\Models\Cid;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaServiceTest extends TestCase
{
    use RefreshDatabase;

    private SlaService $slaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slaService = app(SlaService::class);
    }

    // --- calculateEffectiveDowntime ---

    public function test_returns_zero_for_not_closed_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_OPEN,
            'case_type' => Ticket::CASE_LINK_DOWN,
        ]);

        $this->assertSame(0, $this->slaService->calculateEffectiveDowntime($ticket));
    }

    public function test_returns_zero_for_non_link_down_closed_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_HIGH_LATENCY,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 10:00:00',
        ]);

        $this->assertSame(0, $this->slaService->calculateEffectiveDowntime($ticket));
    }

    public function test_calculates_downtime_minus_pending(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 11:00:00',
        ]);

        $ticket->pendingIntervals()->create([
            'started_at' => '2026-06-01 09:00:00',
            'ended_at' => '2026-06-01 10:00:00',
            'note' => 'Menunggu vendor',
        ]);

        $this->assertSame(120, $this->slaService->calculateEffectiveDowntime($ticket));
        // 180 - 60 = 120
    }

    public function test_ignores_active_pending_interval(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 11:00:00',
        ]);

        $ticket->pendingIntervals()->create([
            'started_at' => '2026-06-01 09:00:00',
            'ended_at' => null,
            'note' => 'Masih pending',
        ]);

        // 180 - 0 (active pending not counted) = 180
        $this->assertSame(180, $this->slaService->calculateEffectiveDowntime($ticket));
    }

    public function test_clamps_downtime_to_zero_when_pending_exceeds_duration(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 10:00:00',
        ]);

        $ticket->pendingIntervals()->create([
            'started_at' => '2026-06-01 08:00:00',
            'ended_at' => '2026-06-01 11:00:00',
            'note' => 'Pending melebihi durasi',
        ]);

        $this->assertSame(0, $this->slaService->calculateEffectiveDowntime($ticket));
        // 120 - 180 = -60 → clamped to 0
    }

    public function test_handles_ticket_without_pending_interval(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 10:00:00',
        ]);

        $this->assertSame(120, $this->slaService->calculateEffectiveDowntime($ticket));
    }

    public function test_sums_multiple_pending_intervals(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 14:00:00',
        ]);

        $ticket->pendingIntervals()->createMany([
            ['started_at' => '2026-06-01 09:00:00', 'ended_at' => '2026-06-01 10:00:00', 'note' => 'Pertama'],
            ['started_at' => '2026-06-01 11:00:00', 'ended_at' => '2026-06-01 12:00:00', 'note' => 'Kedua'],
        ]);

        // 360 - 120 = 240
        $this->assertSame(240, $this->slaService->calculateEffectiveDowntime($ticket));
    }

    // --- calculateMonthlySla ---

    public function test_calculates_monthly_sla_per_cid(): void
    {
        $cid = Cid::factory()->create([
            'cid' => 'CID-SLA-001',
            'sla_percentage' => 99.00,
        ]);

        // 1 ticket closed Link Down di bulan Juni 2026
        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 10:00:00', // 120 menit
        ]);

        $results = $this->slaService->calculateMonthlySla(2026, 6);

        $this->assertCount(1, $results);
        $this->assertSame('CID-SLA-001', $results[0]['cid']);
        $this->assertSame(99.0, $results[0]['sla_target']);
        $this->assertSame(120, $results[0]['total_downtime']);
        $this->assertSame(0, $results[0]['total_pending']);
        $this->assertSame(120, $results[0]['effective_downtime']);

        // Juni 30 hari → 43200 menit
        // SLA = ((43200 - 120) / 43200) * 100 = 99.72
        $this->assertSame(99.72, $results[0]['sla_achieved']);
        $this->assertSame('Aman', $results[0]['status']);
    }

    public function test_monthly_sla_excludes_non_link_down_tickets(): void
    {
        $cid = Cid::factory()->create([
            'cid' => 'CID-EXCLUDE',
            'sla_percentage' => 99.00,
        ]);

        // Link Down ticket
        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 09:00:00', // 60 menit
        ]);

        // Non-Link Down ticket — should be excluded
        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_HIGH_LATENCY,
            'started_at' => '2026-06-01 10:00:00',
            'finished_at' => '2026-06-01 16:00:00', // 360 menit — ignored
        ]);

        $results = $this->slaService->calculateMonthlySla(2026, 6);

        $this->assertCount(1, $results);
        $this->assertSame(60, $results[0]['total_downtime']);
    }

    public function test_monthly_sla_excludes_open_tickets(): void
    {
        $cid = Cid::factory()->create([
            'cid' => 'CID-OPEN',
            'sla_percentage' => 99.00,
        ]);

        // Open ticket — should be excluded
        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_OPEN,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => null,
        ]);

        $results = $this->slaService->calculateMonthlySla(2026, 6);

        $this->assertCount(0, $results);
    }

    public function test_marks_restitution_when_sla_below_target(): void
    {
        $cid = Cid::factory()->create([
            'cid' => 'CID-RESTITUSI',
            'sla_percentage' => 99.50,
        ]);

        // Downtime besar supaya SLA-nya rendah
        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 00:00:00',
            'finished_at' => '2026-06-01 23:59:00', // ~1439 menit
        ]);

        $results = $this->slaService->calculateMonthlySla(2026, 6);

        $this->assertCount(1, $results);
        $this->assertSame('Perlu Restitusi', $results[0]['status']);
        $this->assertLessThan(99.50, $results[0]['sla_achieved']);
    }

    // --- getRestitutionCids ---

    public function test_get_restitution_cids_returns_only_those_needing_restitution(): void
    {
        $cidAman = Cid::factory()->create([
            'cid' => 'CID-AMAN',
            'sla_percentage' => 99.00,
        ]);
        $cidRestitusi = Cid::factory()->create([
            'cid' => 'CID-RESTI',
            'sla_percentage' => 99.50,
        ]);

        Ticket::factory()->create([
            'cid_id' => $cidAman->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 09:00:00', // 60 menit → SLA masih > 99%
        ]);

        Ticket::factory()->create([
            'cid_id' => $cidRestitusi->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 00:00:00',
            'finished_at' => '2026-06-01 23:00:00', // 1380 menit → SLA drop
        ]);

        $restitutions = $this->slaService->getRestitutionCids(2026, 6);

        $this->assertCount(1, $restitutions);
        $this->assertSame('CID-RESTI', $restitutions[0]['cid']);
        $this->assertSame($cidRestitusi->vendor_name, $restitutions[0]['vendor_name']);
        $this->assertSame($cidRestitusi->customer_name, $restitutions[0]['customer_name']);
        $this->assertSame($cidRestitusi->service, $restitutions[0]['service']);
    }

    // --- getDashboardStats ---

    public function test_get_dashboard_stats_returns_correct_counts(): void
    {
        User::factory()->create();

        $cid1 = Cid::factory()->create(['sla_percentage' => 99.00]);
        $cid2 = Cid::factory()->create(['sla_percentage' => 99.00]);

        Ticket::factory()->create([
            'cid_id' => $cid1->id,
            'status' => Ticket::STATUS_OPEN,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => now(),
        ]);
        Ticket::factory()->create([
            'cid_id' => $cid1->id,
            'status' => Ticket::STATUS_PENDING,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => now(),
        ]);
        Ticket::factory()->create([
            'cid_id' => $cid2->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => now()->startOfMonth()->addDay(),
            'finished_at' => now()->startOfMonth()->addDay()->addHours(5), // 300 menit
        ]);

        $stats = $this->slaService->getDashboardStats();

        $this->assertSame(1, $stats['opened_count']);
        $this->assertSame(1, $stats['pending_count']);
        $this->assertSame(1, $stats['closed_count']);
        $this->assertSame(2, $stats['total_cids']);
        $this->assertArrayHasKey('restitution_count', $stats);
        $this->assertIsInt($stats['restitution_count']);
    }

    public function test_get_dashboard_stats_returns_zero_counts_when_no_data(): void
    {
        $stats = $this->slaService->getDashboardStats();

        $this->assertSame(0, $stats['opened_count']);
        $this->assertSame(0, $stats['pending_count']);
        $this->assertSame(0, $stats['closed_count']);
        $this->assertSame(0, $stats['total_cids']);
        $this->assertSame(0, $stats['restitution_count']);
    }
}
