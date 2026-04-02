<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-30 days', '+30 days');

        $startMinutes = fake()->numberBetween(9 * 60, 15 * 60);
        $durationMinutes = fake()->randomElement([30, 45, 60, 75, 90, 120]);
        $endMinutes = min((17 * 60) + 30, $startMinutes + $durationMinutes);

        $startTime = sprintf('%02d:%02d:00', intdiv($startMinutes, 60), $startMinutes % 60);
        $endTime = sprintf('%02d:%02d:00', intdiv($endMinutes, 60), $endMinutes % 60);

        $startAt = Carbon::parse($date->format('Y-m-d').' '.$startTime);
        $endAt = Carbon::parse($date->format('Y-m-d').' '.$endTime);

        return [
            'title' => fake()->sentence(4),
            'objective' => fake()->boolean(70) ? fake()->sentence(12) : null,
            'date' => $startAt->toDateString(),
            'start_time' => $startAt->format('H:i:s'),
            'end_time' => $endAt->format('H:i:s'),
            'status' => fake()->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
            'scheduled_by' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'room_id' => Room::query()->inRandomOrder()->value('id') ?? Room::factory(),
        ];
    }
}
