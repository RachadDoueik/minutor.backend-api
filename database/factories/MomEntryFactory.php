<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MomEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MomEntry>
 */
class MomEntryFactory extends Factory
{
    protected $model = MomEntry::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::query()->inRandomOrder()->value('id') ?? Meeting::factory(),
            'title' => fake()->sentence(4),
            'notes' => fake()->paragraphs(fake()->numberBetween(2, 5), true),
            'summary' => fake()->boolean(60) ? fake()->sentence(16) : null,
            'file_path' => null,
        ];
    }
}
