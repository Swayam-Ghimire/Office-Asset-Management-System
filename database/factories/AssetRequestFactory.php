<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetRequest>
 */
class AssetRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::inRandomOrder()->first()->id,
            'user_id' => User::withoutRole('admin')->inRandomOrder()->first()->id, // Avoid admin user
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'reason' => $this->faker->sentence(),
            'requested_at' => $this->faker->dateTimeBetween('-6 month', 'now'),
            'action_by' => 'Swayam Admin',
            'action_at' => now(),
        ];
    }
}
