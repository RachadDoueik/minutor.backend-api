<?php

namespace Database\Factories;

use App\Models\ActionItem;
use App\Models\MomEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActionItem>
 */
class ActionItemFactory extends Factory
{
    protected $model = ActionItem::class;

    public function definition(): array
    {
        return [
            'mom_entry_id' => MomEntry::query()->inRandomOrder()->value('id') ?? MomEntry::factory(),
            'assigned_to' => fake()->boolean(75) ? (User::query()->inRandomOrder()->value('id') ?? User::factory()) : null,
            'type' => fake()->randomElement(['task', 'decision', 'follow-up', 'note']),
            'description' => fake()->sentence(14),
            'due_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+45 days')->format('Y-m-d') : null,
            'status' => fake()->randomElement(['open', 'in_progress', 'completed', 'cancelled']),
            'file_path' => null,
        ];
    }
}
