<?php

namespace Svanthuijl\Routable\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Tests\TestSupport\Database\Factories\TranslatableExampleFactory;
use Svanthuijl\Routable\Tests\TestSupport\Http\Controllers\ExampleController;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;

class TranslatableExample extends Model implements HasRoutes
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithRoutes;

    /**
     * The attributes that are translatable
     *
     * @var string[]
     */
    public array $translatable = [
        'slug'
    ];

    /**
     * Defining the routes for this model
     * @return void
     */
    public function registerRoutes(): void
    {
        $this->addRoute()
            ->action('exampleAction')
            ->controller(ExampleController::class)
            ->isTranslatable();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TranslatableExampleFactory::new();
    }
}
