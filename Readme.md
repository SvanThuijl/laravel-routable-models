# Associate routes with Eloquent models
This packages allows you to create eloquent models which have their own routes. The routes will automatically be generated when the model is saved and the routes will automatically be registered in the router.

### Localized routes
The package also offers support for localized routes for translatable models. This is built on top of "spatie/laravel-translatable".

## Installation
This package can be installed through Composer.
```shell
composer require svanthuijl/laravel-routable-models
```

### Migrations
Migrations are automatically loaded. Optionally, you can publish the migrations of this package with this command:
```shell 
php artisan vendor:publish --tag="routable-migrations"
```

### Config
Optionally, you can publish the config file of this package with this command:
```shell
php artisan vendor:publish --tag="routable-config"
```
The config file was built to be configured with dotenv variables.
```dotenv
APP_LOCALE=en # This will set the default locale.
APP_LOCALES=en,nl,de #this will set the available locales separated by a comma.
```

### Service provider
The service provider for this package is not discovered automatically. Register the RoutableServiceProvider manually in your config/app.php service providers section. 

**Important!** Make sure that this is the last service provider to be registered since it does check for previously registered routes. 
```php
    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */

        /*
         * Added last to not be overwritten by application routes...
         */
        Svanthuijl\Routable\RoutableServiceProvider::class,
    ])->toArray(),
```

## Using the package

### Implement the package
To make a model routable implement the ```HasRoutes``` interface and use the ```InteractsWithRoutes``` trait in your model.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;

class Example extends Model implements HasRoutes
{
    use InteractsWithRoutes;
//...
```

### Configure the routes
Configuration of the routes is done by adding a ```registerRoutes``` method to your model. 
In this method you call ```$this->addRoute()``` to create a route.
The first property is the name of the route, the second is the used route generator.
By default the name is ```default``` and the used route generator is ```\Svanthuijl\Routable\DefaultRouteGenerator::class```
```php
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
```
This example will create two routes (get and post on the same path) for every model that will be created.

### Retrieve the routes
The default route is accessible with route accessor.
```php
$model = new Example();
$model->slug = 'example-001';
$model->save();

$model->route; // Will return (string) "/example-001"
$model->getRoute('post'); // Will return (string) "/example-001/post"
```

### Handle the routes
The package will automatically register/capture the created routes and will call the configured action. 
The model will be passed to the action as ```$model```.
```php
class ExampleController extends BaseController
{
    public function exampleAction($model): string
    {
        return view('test-view', compact('model'));
    }
}
```

## Localization
The package supports two types of localized routes.
1. Multiple localized routes for translated modes using ```spatie/laravel/translatable```
2. Single route prepended with locale for models with a ```locale``` property

All localized routed will have the locale prepended to the route path.

### Enabling localization for the package
Localized routes are enabled by configuring the ```APP_LOCALE``` and ```APP_LOCALES``` in the dotenv file.

```dotenv
APP_LOCALE=en
APP_LOCALES=en,nl
```

### Implementing localization for a translatable model
For this feature first of all make sure that you have implemented ```spatie/laravel-translatable``` for yur model.
Then add ```isTranslatable()``` to the route configuration. 
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;

class TranslatableExample extends Model implements HasRoutes
{
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
//...
```

### Using localized translatable routes
Now for this model multiple routes will be created.
```php
$model = new TranslatableExample();
$model->slug = [
    'en' => 'slug-in-english',
    'nl' => 'slug-in-dutch',
];
$model->save();

app()->setLocale('en')
$model->route; // Will return (string) "/en/slug-in-english"

app()->setLocale('nl')
$model->route; // Will return (string) "/nl/slug-in-dutch"

$model->getRoute('default', 'en'); // Will return (string) "/en/slug-in-english"
$model->getRoute('default', 'nl'); // Will return (string) "/en/slug-in-dutch"
```

### Handling localized routes
The package will automatically set the locale for the request when a localized route was accessed.
```php
class ExampleController extends BaseController
{
    public function exampleAction($model): string
    {
        app()->locale(); // Will always return the locale for the used route
        return view('test-view', compact('model'));
    }
}
```

### Implementing localization for not translated model
A model can be sat as being localized by calling ```isLocalized()``` on the route generator. The route generator now expects a ```$locale``` property on the model to contain the locale. The property name can be overwritten by adding parameters to the ```isLocalized(true, 'my_locale_property')``` call.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Traits\InteractsWithRoutes;

class LocalizedExample extends Model implements HasRoutes
{
    use InteractsWithRoutes;

    /**
     * Defining the routes for this model
     * @return void
     */
    public function registerRoutes(): void
    {
        $this->addRoute()
            ->action('exampleAction')
            ->controller(ExampleController::class)
            ->isLocalized();
    }
//...
```
Usage:
```php
$model = new LocalizedExample();
$model->locale = 'en';
$model->slug = 'slug-in-english';
$model->save();

$model->route; // Will return (string) "/en/slug-in-english" 

$model = new LocalizedExample();
$model->locale = 'nl';
$model->slug = 'slug-in-dutch';
$model->save();

$model->route; // Will return (string) "/nl/slug-in-dutch" 
```

Also in this case when accessing one of these routes the app()->locale() will be automatically be set to the proper locale.

## Disabling routes
Route generation for a specific model can be disable by overwriting the  ```generateRoutes``` method to return ```false```.

## The route generator
Routes can be manipulated with the options shown in the example below.
You can also replace the default route generator with your own for more advanced implementations. Make sure your custom route generator class implements the ```Svanthuijl\Routable\Interfaces\GeneratesRoutes``` 

```php
    public function registerRoutes(): void
    {
        $this->addRoute('name', Svanthuijl\Routable\DefaultRouteGenerator::class)
            ->action('exampleAction')
            ->controller(ExampleController::class)
            ->fromProperty('slug) // The property to use to generate the route path
            ->isLocalized() // For localized models (cannot be used with isTranslatable)
            ->isTranslatable() // For translatable models (cannot be used with isLocalized)
            ->method('get') // Sets the method the route should listen to
            ->prefix('example') // Prepends the path with "example/"
            ->suffix('example') // Adds "/example" to the path
   }
```

## Testing
Run the tests with:
```shell
composer test
```