<?php

namespace Svanthuijl\Routable\Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Svanthuijl\Routable\Models\Route;
use Svanthuijl\Routable\Tests\TestSupport\Models\Example;
use Svanthuijl\Routable\Tests\TestSupport\Models\ExampleWithDuplicateRouteNames;
use Svanthuijl\Routable\Tests\TestSupport\Models\ExampleWithJson;
use Svanthuijl\Routable\Tests\TestSupport\Models\TranslatableExample;
use Svanthuijl\Routable\Tests\TestCase;

class InteractsWithRoutesTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_throws_an_exception_for_duplicate_route_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Route "route_name" has already been registered');

        ExampleWithDuplicateRouteNames::factory()->create();
    }

    public function test_deletes_routes_when_deleted()
    {
        // Create a product
        $model = Example::factory()->create();

        // Update model
        $model->delete();

        // Test result
        $this->assertDatabaseMissing('routes', [
            'routable_id' => $model->id,
            'routable_type' => Example::class,
        ]);
    }
    public function test_deletes_routes_when_routes_are_disabled()
    {
        // Create a product
        $model = Example::factory()->create();

        // Test if routes were created
        $this->assertDatabaseHas('routes', [
            'routable_id' => $model->id,
            'routable_type' => Example::class,
        ]);

        // Disable routes
        $model->generateRoutes = false;

        // Update product
        $model->touch();

        // Test if routes are deleted
        $this->assertDatabaseMissing('routes', [
            'routable_id' => $model->id,
            'routable_type' => Example::class,
        ]);
    }
    public function test_returns_null_if_route_does_not_exist()
    {
        // Create a product
        $model = Example::factory()->create();

        // Test
        $this->assertNull($model->getRoute('does_not_exist'));
    }

    public function test_stores_routes_in_json_when_created_if_json_column_exists()
    {
        // Create a product
        $model = ExampleWithJson::factory()->create();

        // Test
        $this->assertEquals([
            'default' => $model->slug,
        ], $model->routes_json->toArray());
    }
    public function test_stores_routes_in_json_when_updated_if_json_column_exists()
    {
        // Create a product
        $model = ExampleWithJson::factory()->create();

        $model->routes_json = null;
        $model->save();

        // Update model
        $model->touch();

        // Test
        $this->assertEquals([
            'default' => $model->slug,
        ], $model->routes_json->toArray());
    }
    public function test_deletes_routes_from_json_when_routes_are_disabled_if_json_column_exists()
    {
        // Create a product
        $model = ExampleWithJson::factory()->create();

        // Test if routes were created
        $this->assertNotNull($model->routes_json);

        // Disable routes
        $model->generateRoutes = false;

        // Update product
        $model->touch();

        // Test if routes are deleted
        $this->assertNull($model->routes_json);
    }
    public function test_returns_url_from_json_if_json_column_exists()
    {
        // Create a product
        $model = ExampleWithJson::factory()->create();

        // Test
        $this->assertEquals(url($model->slug), $model->route);
    }

    private static function setupTranslatableEnvironment()
    {
        Config::set('routable-models.locale', 'en');
        Config::set('routable-models.locales', 'en,nl');
    }

    public function test_creates_translatable_routes_on_translatable_model_when_created()
    {
        // Setup environment
        self::setupTranslatableEnvironment();

        // Create a product
        $model = TranslatableExample::factory()->create();

        // Test result
        $this->assertDatabaseHas('routes', [
            'path' => 'en/' . $model->getTranslation('slug', 'en'),
            'routable_id' => $model->id,
            'routable_type' => TranslatableExample::class,
        ]);
        $this->assertDatabaseHas('routes', [
            'path' => 'nl/' . $model->getTranslation('slug', 'nl'),
            'routable_id' => $model->id,
            'routable_type' => TranslatableExample::class,
        ]);
    }
    public function test_creates_translatable_routes_on_translatable_model_when_updated()
    {
        // Setup environment
        self::setupTranslatableEnvironment();

        // Create a product
        $model = TranslatableExample::factory()->create();

        // Truncate routes table
        Route::truncate();

        // Update model
        $model->touch();

        // Test result
        $this->assertDatabaseHas('routes', [
            'path' => 'en/' . $model->getTranslation('slug', 'en'),
            'routable_id' => $model->id,
            'routable_type' => TranslatableExample::class,
        ]);
        $this->assertDatabaseHas('routes', [
            'path' => 'nl/' . $model->getTranslation('slug', 'nl'),
            'routable_id' => $model->id,
            'routable_type' => TranslatableExample::class,
        ]);
    }
    public function test_falls_back_to_the_default_locale_route_when_the_route_does_not_exist_for_the_current_locale()
    {
        // Setup environment
        self::setupTranslatableEnvironment();
        
        // Create a product
        $model = TranslatableExample::factory()->create();

        // Test
        $this->assertNotNull($model->getRoute('default', 'de'));
    }
}
