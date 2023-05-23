<?php

namespace Svanthuijl\Routable\Tests\TestSupport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Svanthuijl\Routable\Tests\TestSupport\Models\Example;
use Svanthuijl\Routable\Tests\TestSupport\Models\ExampleWithDuplicateRouteNames;

/**
 * @extends Factory
 */
class ExampleWithDuplicateRouteNamesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExampleWithDuplicateRouteNames::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->slug(3),
        ];
    }
}
