<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'name' => 'Room '.fake()->unique()->bothify('##??'),
            'location' => fake()->randomElement(['Floor 1', 'Floor 2', 'Floor 3', 'HQ', 'Branch']),
            'capacity' => fake()->numberBetween(4, 30),
            'description' => fake()->boolean(70) ? fake()->sentence(12) : null,
        ];
    }
}
