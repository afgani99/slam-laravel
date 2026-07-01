<?php

namespace Tests\Feature;

use App\Models\Cid;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaMonthlyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_monthly_sla_page_can_be_rendered(): void
    {
        $this->actingAs($this->user)
            ->get(route('sla.monthly'))
            ->assertOk();
    }

    public function test_monthly_sla_page_shows_no_results_when_empty(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sla.monthly', ['tahun' => 2026, 'bulan' => 6]));

        $response->assertOk();
        $response->assertSee('2026');
        $response->assertSee('Juni');
    }

    public function test_monthly_sla_page_shows_cid_with_ticket_data(): void
    {
        $cid = Cid::factory()->create([
            'cid' => 'CID-SLA-001',
            'vendor_name' => 'Vendor Test',
            'customer_name' => 'Customer Test',
            'service' => 'Internet Dedicated',
            'sla_percentage' => 99.00,
        ]);

        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 10:00:00',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('sla.monthly', ['tahun' => 2026, 'bulan' => 6]));

        $response->assertOk();
        $response->assertSee('CID-SLA-001');
        $response->assertSee('Vendor Test');
        $response->assertSee('Customer Test');
        $response->assertSee('Internet Dedicated');
        $response->assertSee('Aman');
    }

    public function test_monthly_sla_page_filters_by_month(): void
    {
        $cid = Cid::factory()->create([
            'cid' => 'CID-FILTER',
            'vendor_name' => 'Vendor Filter',
            'customer_name' => 'Customer Filter',
            'service' => 'Internet Dedicated',
            'sla_percentage' => 99.00,
        ]);

        // Ticket di Juni
        Ticket::factory()->create([
            'cid_id' => $cid->id,
            'status' => Ticket::STATUS_CLOSED,
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-01 08:00:00',
            'finished_at' => '2026-06-01 10:00:00',
        ]);

        // Filter Juli — tidak ada data
        $response = $this->actingAs($this->user)
            ->get(route('sla.monthly', ['tahun' => 2026, 'bulan' => 7]));

        $response->assertOk();
        $response->assertDontSee('CID-FILTER');
    }
}
