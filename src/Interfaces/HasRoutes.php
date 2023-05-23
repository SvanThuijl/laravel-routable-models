<?php

namespace Svanthuijl\Routable\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Svanthuijl\Routable\DefaultRouteGenerator;


/**
 * @property Collection $registeredRoutes;
 */
interface HasRoutes
{
    public static function updateOrCreateRoutes(HasRoutes $model): void;
    public function getRegisteredRoutes(): Collection;

    public function routes(): MorphMany;
    public function getRoute(string $name = 'default', $locale = null, bool $noJson = false): string | null;

    public function addRoute(string $name = 'default', string|null $routeGenerator = null): GeneratesRoutes;

    public function generateRoutes(): bool;
    public function registerRoutes(): void;
    public function hasRoutesJsonAttribute(): bool;
}