<?php

/**
 * Check if there is a root route configured by the main application.
 * If this is not the case root route will be configured for a routable model
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

try { Route::getRoutes()->match(request()->create('/')); }
catch (Exception $e)
{
    Route::get('/', function () {
        $route = Svanthuijl\Routable\Models\Route::where('path', '')
            ->whereMethod('get')
            ->first();
        if ($route === null)
            abort(404);
        return $route->call();
    });
}

/**
 * Create the catch-all route for all the methods
 */
foreach (config('routable-models.methods') as $method)
{
    $bindName = 'routable' . Str::ucfirst($method);
    Route::$method('{' . $bindName. '}', function () use ($bindName)
    {
        $route = null;
        if (func_num_args())
            $route = func_get_arg(0);
        if ($route === null)
            abort(404);
        return $route->call();
    })
    ->name('routable.' . $method);

}
