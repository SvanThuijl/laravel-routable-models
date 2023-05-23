<?php

namespace Svanthuijl\Routable\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Tests\TestSupport\Database\Factories\ExampleFactory;
use Svanthuijl\Routable\Tests\TestSupport\Http\Controllers\ExampleController;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;

class Example extends Model implements HasRoutes
{
    use HasFactory;
    use InteractsWithRoutes;

    /**
     * Variable to determine if routes should be generated
     * @var bool
     */
    public bool $generateRoutes = true;

    /**
     * Defining the routes for this model
     * @return void
     */
    public function registerRoutes(): void
    {
        $this->addRoute()
            ->action('exampleAction')
            ->controller(ExampleController::class);
        $this->addRoute('post')
            ->action('exampleAction')
            ->controller(ExampleController::class)
            ->method('post');
    }

    /**
     * Determine if the routes should be generated
     * This method can be overwritten in the model itself
     *
     * @return bool
     */
    public function generateRoutes(): bool
    {
        return $this->generateRoutes;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return ExampleFactory::new();
    }


}
