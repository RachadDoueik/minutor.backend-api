<?php

namespace Database\Factories;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feature>
 */
class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(fake()->numberBetween(1, 3), true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'description' => fake()->boolean(60) ? fake()->sentence(10) : null,
        ];
    }
}
