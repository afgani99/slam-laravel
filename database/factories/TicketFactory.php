<?php

namespace Database\Factories;

use App\Models\Cid;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'ticket_number' => 'nusa-' . fake()->unique()->regexify('[0-9]{2}[0-1][0-9][0-3][0-9][A-Z]{3}'),
            'cid_id' => Cid::factory(),
            'vendor_ticket_number' => 'VND-' . fake()->unique()->numerify('####'),
            'case_type' => fake()->randomElement(Ticket::CASE_TYPES),
            'started_at' => fake()->dateTimeBetween('-30 days'),
            'finished_at' => null,
            'rfo_action' => null,
            'status' => Ticket::STATUS_OPEN,
            'closed_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_CLOSED,
            'finished_at' => fake()->dateTimeBetween($attributes['started_at'], $attributes['started_at']->format('Y-m-d H:i:s') === 'now' ? 'now' : '+1 day'),
            'closed_at' => now(),
            'rfo_action' => fake()->sentence(),
        ]);
    }

    public function linkDown(): static
    {
        return $this->state(fn () => [
            'case_type' => Ticket::CASE_LINK_DOWN,
        ]);
    }
}
