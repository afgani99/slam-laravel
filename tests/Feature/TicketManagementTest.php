<?php

namespace Tests\Feature;

use App\Models\Cid;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_pending_resume_and_close_ticket(): void
    {
        $user = User::factory()->create();
        $cid = Cid::create([
            'cid' => 'CID-001',
            'vendor_name' => 'Vendor Test',
            'customer_name' => 'Customer Test',
            'service' => 'Internet Dedicated',
            'sla_percentage' => 99.50,
        ]);

        $this->actingAs($user)
            ->post(route('tickets.store'), [
                'cid_id' => $cid->id,
                'vendor_ticket_number' => 'VND-001',
                'case_type' => Ticket::CASE_LINK_DOWN,
                'started_at' => '2026-06-28 08:00:00',
            ])
            ->assertRedirect();

        $ticket = Ticket::firstOrFail();

        $this->assertSame(Ticket::STATUS_OPEN, $ticket->status);
        $this->assertStringStartsWith('nusa-', $ticket->ticket_number);

        $this->actingAs($user)
            ->post(route('tickets.pending', $ticket), [
                'started_at' => '2026-06-28 09:00:00',
                'note' => 'Menunggu konfirmasi pelanggan',
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_PENDING, $ticket->status);
        $this->assertCount(1, $ticket->pendingIntervals);

        $this->actingAs($user)
            ->post(route('tickets.resume', $ticket), [
                'ended_at' => '2026-06-28 10:00:00',
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_OPEN, $ticket->status);
        $this->assertNotNull($ticket->pendingIntervals()->first()->ended_at);

        $this->actingAs($user)
            ->post(route('tickets.close', $ticket), [
                'finished_at' => '2026-06-28 11:00:00',
                'rfo_action' => 'Perbaikan perangkat vendor',
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_CLOSED, $ticket->status);
        $this->assertSame('2026-06-28 11:00:00', $ticket->closed_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-28 08:00:00', $ticket->started_at?->format('Y-m-d H:i:s'));
        $this->assertNotSame($ticket->started_at?->format('Y-m-d H:i:s'), $ticket->closed_at?->format('Y-m-d H:i:s'));
        $this->assertSame('Perbaikan perangkat vendor', $ticket->rfo_action);
    }

    public function test_ticket_pages_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $cid = Cid::create([
            'cid' => 'CID-002',
            'vendor_name' => 'Vendor Test',
            'customer_name' => 'Customer Test',
            'service' => 'Internet Dedicated',
            'sla_percentage' => 99.50,
        ]);
        $ticket = Ticket::create([
            'ticket_number' => 'nusa-280626AAA',
            'cid_id' => $cid->id,
            'vendor_ticket_number' => 'VND-002',
            'case_type' => Ticket::CASE_LINK_DOWN,
            'started_at' => '2026-06-28 08:00:00',
            'status' => Ticket::STATUS_OPEN,
        ]);

        $this->actingAs($user)->get(route('tickets.index'))->assertOk();
        $this->actingAs($user)->get(route('tickets.create'))->assertOk();
        $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk();
        $this->actingAs($user)->get(route('tickets.edit', $ticket))->assertOk();
    }
}
