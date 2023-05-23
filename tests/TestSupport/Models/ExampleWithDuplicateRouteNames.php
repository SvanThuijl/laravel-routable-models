<?php

namespace Svanthuijl\Routable\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Tests\TestSupport\Database\Factories\ExampleFactory;
use Svanthuijl\Routable\Tests\TestSupport\Database\Factories\ExampleWithDuplicateRouteNamesFactory;
use Svanthuijl\Routable\Tests\TestSupport\Http\Controllers\ExampleController;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;

class ExampleWithDuplicateRouteNames extends Model implements HasRoutes
{
    use HasFactory;
    use InteractsWithRoutes;

    protected $table = 'examples';


    /**
     * Defining the routes for this model
     * @return void
     */
    public function registerRoutes(): void
    {
        $this->addRoute('route_name')
            ->action('exampleAction')
            ->controller(ExampleController::class);
        $this->addRoute('route_name')
            ->action('exampleAction')
            ->controller(ExampleController::class)
            ->method('post');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return ExampleWithDuplicateRouteNamesFactory::new();
    }


}
