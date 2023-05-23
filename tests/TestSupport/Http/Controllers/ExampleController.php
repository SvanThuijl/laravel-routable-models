<?php

namespace Svanthuijl\Routable\Tests\TestSupport\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class ExampleController extends BaseController
{
    public function exampleAction($model): string
    {
        return app()->getLocale();
    }
}
