<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // 1. Pick the status first
        $status = $this->faker->randomElement(['available', 'not_available', 'under_maintenance']);

        // 2. Determine the condition based on that status
        $condition = match ($status) {
            'under_maintenance' => 'damaged', // Only damaged assets go to maintenance
            'available' => $this->faker->randomElement(['new', 'good']),
            'not_available' => $this->faker->randomElement(['new', 'good', 'damaged']),
            default => 'good',
        };

        return [
            'model_name' => $this->faker->unique()->word(3, true),
            'brand' => $this->faker->company(),
            'purchase_date' => $this->faker->date(),
            'condition' => $condition,
            'status' => $status,
            'category_id' => Category::inRandomOrder()->first()->id,
        ];
    }
}
