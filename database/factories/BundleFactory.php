<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Bundle;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bundle>
 */
class BundleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word().'.zip',
            'description' => fake()->sentence(),
            'size' => fake()->randomFloat(2, 0.1, 10.0),
            'file_path' => 'bundles/'.fake()->uuid().'.zip',
            'application_id' => Application::factory(),
            'channel_id' => Channel::factory(),
        ];
    }
}
