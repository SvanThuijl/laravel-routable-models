<?php

namespace Svanthuijl\Routable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Svanthuijl\Routable\DefaultRouteGenerator;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Interfaces\GeneratesRoutes;
use Svanthuijl\Routable\Models\Route;
use function url;

trait InteractsWithRoutes
{
    public array|null $registeredRoutes = null;

    /**
     * Boot the trait
     */
    protected static function bootInteractsWithRoutes(): void
    {
        // Define the deleting event
        static::deleting(function($model) {
            $model->routes()->delete();
        });

        // Define the saving event
        static::saved(function ($model) {
            static::updateOrCreateRoutes($model);
        });
    }
    /**
     * Generate the routes for the model when saved
     *
     * @param HasRoutes $model
     * @return void
     */
    public static function updateOrCreateRoutes(HasRoutes $model): void
    {
        // Register routes
        $modelRoutes = collect();
        $registeredRoutes = $model->getRegisteredRoutes();

        // Check if routes should be generated
        if (!$model->generateRoutes() ||
            $model->registeredRoutes == null ||
            !count($model->registeredRoutes))
        {
            // Delete existing routes
            $model->routes()->delete();

            // Clear routes_json property if it exists
            if ($model->hasRoutesJsonAttribute())
            {
                $model->{config('routable-models.routes_json_column')} = null;
                $model->saveQuietly();
            }

            return; // Kill the event handler
        }

        // Iterate and create routes
        $registeredRoutes->each(
            fn (GeneratesRoutes $routeDefinition) =>
                $routeDefinition->forModel($model)
                    ->getPaths()
                    ->each(
                        fn (string|null $locale, string $path) =>
                            $modelRoutes->add(
                                $model->routes()->updateOrCreate(
                                [
                                    'method' => $routeDefinition->getMethod(),
                                    'name' => $routeDefinition->getName($locale),
                                ],
                                [
                                    'action' => $routeDefinition->getAction(),
                                    'controller' => $routeDefinition->getController(),
                                    'locale' => $locale,
                                    'path' => $path,
                                ]
                            )
                            )
                    )
        );

        // Sync to delete old routes
        $model->routes()->whereNotIn('id', $modelRoutes->pluck('id'));

        // Store routes_json property if it exists
        if ($model->hasRoutesJsonAttribute())
        {
            $model->{config('routable-models.routes_json_column')} = $model->routes->pluck('path', 'name');
            $model->saveQuietly();
        }
    }
    /**
     * Get the registered the routes
     * @return \Illuminate\Support\Collection
     */
    public function getRegisteredRoutes(): Collection
    {
        if ($this->registeredRoutes === null)
            $this->registerRoutes();
        return collect($this->registeredRoutes);
    }



    /**
     * Relationship to the route
     * @return mixed
     */
    public function routes(): MorphMany
    {
        return $this->morphMany(Route::class, 'routable');
    }
    /**
     * Returns the default route as a string
     * @return string
     */
    protected function getRouteAttribute(): string
    {
        return strval($this->getRoute());
    }
    /**
     * Return a route based on the name.
     *
     * @param string $name
     * @param $locale
     * @param bool $noJson
     * @return string|null
     */
    public function getRoute(string $name = 'default', $locale = null, bool $noJson = false): string | null
    {
        $localeName = $name;
        if ($locale)
            $localeName .= '.' . $locale;

        // First check json
        if ($this->hasRoutesJsonAttribute() &&
            isset($this->{config('routable-models.routes_json_column')}[($localeName !== null ? $localeName : '')]) &&
            !$noJson)
            return url('/' . $this->{config('routable-models.routes_json_column')}[($localeName !== null ? $localeName : '')]);

        // Load route
        $route = $this->routes->where('name', $localeName)->first();

        if ($route)
            return $route;

        // Route not found, fall back to default locale
        if ($locale !== config('routable-models.locale'))
            return $this->getRoute($name, config('routable-models.locale'), $noJson);

        // Route does not exist
        return null;
    }



    /**
     * The method used to register a route definition
     * @param string $name
     * @param string|null $routeGenerator
     * @return GeneratesRoutes
     */
    public function addRoute(string $name = 'default', string|null $routeGenerator = null): GeneratesRoutes
    {
        if (isset($this->registeredRoutes[$name]))
            throw new InvalidArgumentException('Route "' . $name . '" has already been registered');

        if ($routeGenerator === null)
            $routeGenerator = config('routable-models.generator', DefaultRouteGenerator::class);

        $route =  new $routeGenerator($name);
        $this->registeredRoutes[$name] = $route;

        return $route;
    }

    /**
     * Determine if the routes should be generated
     * This method can be overwritten in the model itself
     *
     * @return bool
     */
    public function generateRoutes(): bool
    {
        return true;
    }
    /**
     * The method in whoch the route definitions should be registered in the child class.
     * @return void
     */
    public function registerRoutes(): void {}



    /**
     * Determine if the routes should also be stored as json
     *
     * @return bool
     */
    public function hasRoutesJsonAttribute(): bool
    {
        return Schema::hasColumn($this->getTable(), config('routable-models.routes_json_column'));
    }
}
