<?php

namespace Database\Factories;

use App\Models\Agenda;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agenda>
 */
class AgendaFactory extends Factory
{
    protected $model = Agenda::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::query()->inRandomOrder()->value('id') ?? Meeting::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->boolean(60) ? fake()->sentence(12) : null,
        ];
    }
}
