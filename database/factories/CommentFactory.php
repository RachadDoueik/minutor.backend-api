<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'meeting_id' => Meeting::query()->inRandomOrder()->value('id') ?? Meeting::factory(),
            'text' => fake()->sentence(18),
        ];
    }
}
