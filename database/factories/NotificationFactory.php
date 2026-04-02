<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'type' => fake()->randomElement(['meeting_reminder', 'meeting_updated', 'action_item_due', 'comment_added']),
            'text' => fake()->sentence(14),
            'link' => fake()->boolean(60) ? '/app/'.fake()->randomElement(['meetings', 'action-items', 'notifications']).'/'.fake()->numberBetween(1, 200) : null,
            'is_read' => fake()->boolean(40),
        ];
    }
}
