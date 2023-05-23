<?php

namespace Svanthuijl\Routable\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Spatie\Translatable\HasTranslations;
use Svanthuijl\Routable\DefaultRouteGenerator;
use Svanthuijl\Routable\Tests\TestSupport\Models\Example;
use Svanthuijl\Routable\Tests\TestCase;
use Svanthuijl\Routable\Tests\TestSupport\Models\TranslatableExample;

class DefaultRouteGeneratorTest extends TestCase
{
    private static function setupTranslatableEnvironment()
    {
        Config::set('routable-models.locale', 'en');
        Config::set('routable-models.locales', 'en,nl');
    }
    
    public function test_can_be_created()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $this->assertIsObject($routeGenerator);
    }

    public function test_name_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator('test_route_name');
        $this->assertEquals('test_route_name', $routeGenerator->getName());
    }
    public function test_action_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->action('testAction');
        $this->assertEquals('testAction', $routeGenerator->getAction());
    }
    public function test_controller_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->controller('testController');
        $this->assertEquals('testController', $routeGenerator->getController());
    }
    public function test_from_property_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->fromProperty('slug');
        $this->assertEquals('slug', $routeGenerator->getFromProperty());
    }
    public function test_is_localized_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();

        $routeGenerator->isLocalized(true);
        $this->assertTrue($routeGenerator->getIsLocalized());

        $routeGenerator->isLocalized(false);
        $this->assertFalse($routeGenerator->getIsLocalized());
    }
    public function test_is_localized_locale_property_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();

        $routeGenerator->isLocalized(true, 'locale_property');
        $this->assertEquals('locale_property', $routeGenerator->getLocaleProperty());
    }
    public function test_is_translatable_is_stored_and_can_be_retrieved()
    {
        self::setupTranslatableEnvironment();

        $routeGenerator = new DefaultRouteGenerator();

        $routeGenerator->isTranslatable(true);
        $this->assertTrue($routeGenerator->getIsTranslatable());

        $routeGenerator->isTranslatable(false);
        $this->assertFalse($routeGenerator->getIsTranslatable());
    }
    public function test_method_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->method('put');
        $this->assertEquals('put', $routeGenerator->getMethod());
    }
    public function test_prefix_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->prefix('test_prefix');
        $this->assertEquals('test_prefix', $routeGenerator->getPrefix());
    }
    public function test_suffix_is_stored_and_can_be_retrieved()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->suffix('test_suffix');
        $this->assertEquals('test_suffix', $routeGenerator->getSuffix());
    }

    public function test_default_method_is_get()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $this->assertEquals('get', $routeGenerator->getMethod());
    }
    public function test_default_name_is_default()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $this->assertEquals('default', $routeGenerator->getName());
    }
    public function test_default_is_localized_is_false()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $this->assertFalse($routeGenerator->getIsLocalized());
    }
    public function test_default_is_localized_locale_property_is_locale()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $this->assertEquals('locale', $routeGenerator->getLocaleProperty());}
    public function test_default_is_translatable_is_false()
    {
        $routeGenerator = new DefaultRouteGenerator();
        $this->assertFalse($routeGenerator->getIsTranslatable());
    }

    public function test_throws_an_exception_for_not_supported_methods()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Method "not_existing_method" is not supported due to routable-models configuration');

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->method('not_existing_method');
    }
    public function test_throws_an_exception_for_localized_and_translatable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Routable model cannot be localized and translatable');

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isLocalized();
        $routeGenerator->isTranslatable();
    }
    public function test_throws_an_exception_for_translatable_and_localized()
    {
        self::setupTranslatableEnvironment();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Routable model cannot be localized and translatable');

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable();
        $routeGenerator->isLocalized();
    }
    public function test_throws_an_exception_for_translatable_without_translatable_config()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Routable model cannot be translatable due to missing configuration in routable-models');

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable();
    }

    public function test_name_can_be_retrieve_localized()
    {
        self::setupTranslatableEnvironment();

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable();
        $this->assertEquals('default.en', $routeGenerator->getName('en'));
    }

    public function test_throws_an_exception_if_from_property_does_not_exist_on_model()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"slug" does not exists on "' . Example::class . '"');

        $model = new Example();
        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->forModel($model);
    }
    public function test_throws_an_exception_if_locale_property_does_not_exist_on_localized_route()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"locale" does not exists on "' . Example::class . '"');

        $model = new Example();
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isLocalized()
            ->forModel($model);
    }
    public function test_throws_an_exception_if_locale_property_is_empty_on_localized_route()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"locale" is empty on "' . Example::class . '"');

        $model = new Example();
        $model->locale = '';
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isLocalized()
            ->forModel($model);
    }
    public function test_throws_an_exception_if_model_does_not_use_spatie_translatable_on_translatable_route()
    {
        Config::set('routable-models.locale', 'en');
        Config::set('routable-models.locales', 'en,nl');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"' . Example::class . '" does not use "' . HasTranslations::class . '"');

        $model = new Example();
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable()
            ->forModel($model);
    }
    public function test_throws_an_exception_if_model_from_property_is_not_configured_to_be_translated_route()
    {
        self::setupTranslatableEnvironment();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"not_translatable_slug" is not configured to be translatable in "' . TranslatableExample::class . '"');

        $model = new TranslatableExample();
        $model->not_translatable_slug = 'not-translatable';
        $model->slug = [
            'en' => 'test-slug-in-english',
            'nl' => 'test-slug-in-dutch',
        ];

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->fromProperty('not_translatable_slug')
            ->isTranslatable()
            ->forModel($model);
    }

    public function test_generates_a_single_path()
    {
        $model = new Example();
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->forModel($model);

        $pathsExpected = collect(['test-slug' => null]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
    public function test_generates_a_single_path_with_locale()
    {
        $model = new Example();
        $model->locale = 'en';
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isLocalized()
            ->forModel($model);

        $pathsExpected = collect(['en/test-slug' => null]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
    public function test_generates_a_single_path_with_prefix()
    {
        $model = new Example();
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->prefix('test-prefix')
            ->forModel($model);

        $pathsExpected = collect(['test-prefix/test-slug' => null]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
    public function test_generates_a_single_path_with_suffix()
    {
        $model = new Example();
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->suffix('test-suffix')
            ->forModel($model);

        $pathsExpected = collect(['test-slug/test-suffix' => null]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
    public function test_generates_a_single_path_with_locale_and_prefix_and_suffix()
    {
        $model = new Example();
        $model->locale = 'en';
        $model->slug = 'test-slug';

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isLocalized()
            ->prefix('test-prefix')
            ->suffix('test-suffix')
            ->forModel($model);

        $pathsExpected = collect(['en/test-prefix/test-slug/test-suffix' => null]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }

    public function test_generates_multiple_paths()
    {
        self::setupTranslatableEnvironment();

        $model = new TranslatableExample();
        $model->slug = [
            'en' => 'test-slug-in-english',
            'nl' => 'test-slug-in-dutch',
        ];

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable()
            ->forModel($model);

        $pathsExpected = collect([
            'en/test-slug-in-english' => 'en',
            'nl/test-slug-in-dutch' => 'nl',
        ]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
    public function test_generates_multiple_paths_with_prefix()
    {
        self::setupTranslatableEnvironment();

        $model = new TranslatableExample();
        $model->slug = [
            'en' => 'test-slug-in-english',
            'nl' => 'test-slug-in-dutch',
        ];

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable()
            ->prefix('test-prefix')
            ->forModel($model);

        $pathsExpected = collect([
            'en/test-prefix/test-slug-in-english' => 'en',
            'nl/test-prefix/test-slug-in-dutch' => 'nl',
        ]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
    public function test_generates_multiple_paths_with_suffix()
    {
        self::setupTranslatableEnvironment();

        $model = new TranslatableExample();
        $model->slug = [
            'en' => 'test-slug-in-english',
            'nl' => 'test-slug-in-dutch',
        ];

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable()
            ->suffix('test-suffix')
            ->forModel($model);

        $pathsExpected = collect([
            'en/test-slug-in-english/test-suffix' => 'en',
            'nl/test-slug-in-dutch/test-suffix' => 'nl',
        ]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
    public function test_generates_multiple_paths_with_prefix_and_suffix()
    {
        self::setupTranslatableEnvironment();

        $model = new TranslatableExample();
        $model->slug = [
            'en' => 'test-slug-in-english',
            'nl' => 'test-slug-in-dutch',
        ];

        $routeGenerator = new DefaultRouteGenerator();
        $routeGenerator->isTranslatable()
            ->prefix('test-prefix')
            ->suffix('test-suffix')
            ->forModel($model);

        $pathsExpected = collect([
            'en/test-prefix/test-slug-in-english/test-suffix' => 'en',
            'nl/test-prefix/test-slug-in-dutch/test-suffix' => 'nl',
        ]);
        $pathsGenerated = $routeGenerator->getPaths();

        $this->assertEquals($pathsExpected, $pathsGenerated);
    }
}