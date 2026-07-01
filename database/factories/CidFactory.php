<?php

namespace Database\Factories;

use App\Models\Cid;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cid>
 */
class CidFactory extends Factory
{
    protected $model = Cid::class;

    public function definition(): array
    {
        return [
            'cid' => 'CID-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 3, '0', STR_PAD_LEFT),
            'vendor_name' => fake()->company(),
            'customer_name' => fake()->company(),
            'service' => fake()->randomElement(['Internet Dedicated', 'MPLS VPN', 'Metro Ethernet', 'IP Transit']),
            'sla_percentage' => fake()->randomFloat(2, 95, 99.99),
        ];
    }
}
