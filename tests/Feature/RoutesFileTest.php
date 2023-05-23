<?php

namespace Svanthuijl\Routable\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Svanthuijl\Routable\Tests\TestSupport\Models\Example;
use Svanthuijl\Routable\Tests\TestSupport\Models\TranslatableExample;
use Svanthuijl\Routable\Tests\TestCase;

class RoutesFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_200_to_root_get_request()
    {
        // Create model
        Example::factory()->create([
            'slug' => '',
        ]);

        $response = $this->get('/');

        // Assert response status code
        $response->assertStatus(200);
    }
    public function test_responds_200_to_existing_get_request()
    {
        // Create model
        Example::factory()->create([
            'slug' => 'test-route',
        ]);

        $response = $this->get('/test-route');

        // Assert response status code
        $response->assertStatus(200);
    }
    public function test_responds_200_to_existing_post_request()
    {
        // Create model
        Example::factory()->create([
            'slug' => 'test-route',
        ]);

        $response = $this->post('/test-route');

        // Assert response status code
        $response->assertStatus(200);
    }
    public function test_responds_404_to_not_existing_get_request()
    {
        $response = $this->get('/test-route');

        // Assert response status code
        $response->assertStatus(404);
    }
    public function test_responds_404_to_not_existing_post_request()
    {
        $response = $this->post('/test-route');

        // Assert response status code
        $response->assertStatus(404);
    }
    public function test_responds_404_to_root_get_request_if_not_exists()
    {
        $response = $this->get('/');
        $response->assertStatus(404);
    }

    public function test_locale_route_sets_locale_for_application_with_localized_model()
    {
        // Create a product
        $model = Example::factory()->create([
            'locale' => 'de',
        ]);

        $response = $this->get($model->route);
        $response->assertContent('de');
    }

    private static function setupTranslatableEnvironment()
    {
        Config::set('routable-models.locale', 'en');
        Config::set('routable-models.locales', 'en,nl');
    }
    public function test_locale_route_sets_locale_for_application_with_localized_routes()
    {
        // Setup translatable environment
        self::setupTranslatableEnvironment();

        // Create a product
        $model = TranslatableExample::factory()->create();

        $response = $this->get($model->getRoute('default', 'en'));
        $response->assertContent('en');

        $response = $this->get($model->getRoute('default', 'nl'));
        $response->assertContent('nl');
    }
}
