<?php

namespace Database\Factories;

use App\Models\Agenda;
use App\Models\AgendaTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgendaTopic>
 */
class AgendaTopicFactory extends Factory
{
    protected $model = AgendaTopic::class;

    public function definition(): array
    {
        return [
            'agenda_id' => Agenda::query()->inRandomOrder()->value('id') ?? Agenda::factory(),
            'owner_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->boolean(70) ? fake()->sentence(16) : null,
            'order' => fake()->numberBetween(1, 20),
            'estimated_duration' => fake()->boolean(80) ? fake()->randomElement([5, 10, 15, 20, 30, 45, 60]) : null,
        ];
    }
}
