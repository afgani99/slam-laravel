<?php

namespace Tests\Feature;

use App\Models\Cid;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CidSlaGraphTest extends TestCase
{
    use RefreshDatabase;

    public function test_cid_detail_page_shows_sla_6_month_section(): void
    {
        $user = User::factory()->create();
        $cid = Cid::factory()->create([
            'cid' => 'CID-GRAPH',
            'sla_percentage' => 99.00,
        ]);

        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('cids.show', $cid));

        $response->assertOk();
        $response->assertSee('Grafik SLA 6 Bulan');
        $response->assertSee('data-sla-6-months');
        $response->assertSee('CID-GRAPH');
    }
}
