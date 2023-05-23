<?php

namespace Svanthuijl\Routable\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Svanthuijl\Routable\Models\Route;
use Svanthuijl\Routable\Tests\TestSupport\Models\Example;
use Svanthuijl\Routable\Tests\TestSupport\Models\NotImplemented;
use Svanthuijl\Routable\Tests\TestCase;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;
use Symfony\Component\Console\Exception\RuntimeException;

class GenerateRoutesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_an_error_message_if_the_model_does_not_exist()
    {
        $this->artisan('routable-models:generate', [
            'model' => 'App\\Models\\NotExistingModelName',
        ])
            ->expectsOutput('"App\Models\NotExistingModelName" does not exist.')
            ->assertExitCode(0);
    }
    public function test_it_displays_an_error_message_if_the_model_is_not_routable()
    {
        $this->artisan('routable-models:generate', [
            'model' => NotImplemented::class, // This is a model know to not be routable
        ])
            ->expectsOutput('"' . NotImplemented::class . '" does not implement "' . InteractsWithRoutes::class . '".')
            ->assertExitCode(0);
    }
    public function test_it_displays_an_error_message_if_model_argument_is_missing()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "model").');

        $this->artisan('routable-models:generate');
    }
    public function test_it_queues_at_least_one_batch_of_models_to_be_generated()
    {
        $this->artisan('routable-models:generate', [
            'model' => Example::class, // This is a model know to be routable
        ])
            ->expectsOutputToContain('Batch created for "' . Example::class . '" from ')
            ->assertExitCode(0);
    }

    public function test_it_does_run_the_updateOrCreateRoutes_on_the_routable_models()
    {
        // Create a product
        $product = Example::factory()->create();

        // Truncate routes table
        Route::truncate();

        // Run the command
        $this->artisan('routable-models:generate', [
            'model' => Example::class, // This is a model know to be routable
        ])->run();

        // // Test result
        $this->assertDatabaseHas('routes', [
            'routable_id' => $product->id,
            'routable_type' => Example::class,
        ]);
    }
}
