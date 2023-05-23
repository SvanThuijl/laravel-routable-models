<?php

namespace Svanthuijl\Routable\Tests\TestSupport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Svanthuijl\Routable\Tests\TestSupport\Models\TranslatableExample;

/**
 * @extends Factory
 */
class TranslatableExampleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TranslatableExample::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => [
                'en' => fake()->slug(3),
                'nl' => fake()->slug(3),
            ],
        ];
    }
}
