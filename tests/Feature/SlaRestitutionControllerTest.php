<?php

namespace Tests\Feature;

use App\Models\Cid;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaRestitutionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_restitution_page_can_be_rendered(): void
    {
        $this->actingAs($this->user)
            ->get(route('sla.restitution'))
            ->assertOk();
    }

    public function test_restitution_page_shows_only_cids_below_target_sla(): void
    {
        $safeCid = Cid::factory()->create([
            'cid' => 'CID-AMAN',
            'vendor_name' => 'Vendor Aman',
            'customer_name' => 'Customer Aman',
            'service' => 'Internet Dedicated',
            'sla_percentage' => 99.00,
        ]);
        $restitutionCid = Cid::factory()->create([
            'cid' => 'CID-RESTI',
            'vendor_name' => 'Vendor Resti',
            'customer_name' => 'Customer Resti',
            'service' => 'MPLS VPN',
            'sla_percentage' => 99.50,
        ]);

        Ticket::factory()->create([
            'cid_id' => $safeCid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 09:00:00',
        ]);

        Ticket::factory()->create([
            'cid_id' => $restitutionCid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 00:00:00',
            'finished_at' => '2026-06-01 23:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('sla.restitution', ['tahun' => 2026, 'bulan' => 6]));

        $response->assertOk();
        $response->assertSee('CID-RESTI');
        $response->assertSee('Vendor Resti');
        $response->assertSee('Customer Resti');
        $response->assertSee('MPLS VPN');
        $response->assertDontSee('CID-AMAN');
        $response->assertDontSee('Vendor Aman');
    }

    public function test_restitution_page_shows_empty_message_when_no_restitution(): void
    {
        $cid = Cid::factory()->create([
            'cid' => 'CID-NO-RESTI',
            'sla_percentage' => 99.00,
        ]);

        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 09:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('sla.restitution', ['tahun' => 2026, 'bulan' => 6]));

        $response->assertOk();
        $response->assertSee('Tidak ada CID yang perlu restitusi');
        $response->assertDontSee('CID-NO-RESTI');
    }
}
