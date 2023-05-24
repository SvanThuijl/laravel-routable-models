<?php

return [
    /**
     * The default locale to be used when using localized routes
     */
    'locale' => env('APP_LOCALE', null),

    /**
     * The available localizations for routes
     */
    'locales' => function () {
        $locale = env('APP_LOCALE', null);
        $localesStr = env('APP_LOCALES');

        if ($locale === null &&
            $localesStr === null)
            return [];

        if ($localesStr === null)
            return  [$locale];

        return str($localesStr)->explode(',')
            ->mapWithKeys(fn ($locale) => [$locale => $locale]);
    },

    /**
     *
     */
    'generator' => \Svanthuijl\Routable\DefaultRouteGenerator::class,

    /**
     * The available methods for the routes
     */
    'methods' => [
        'get',
        'post',
        'put',
        'patch',
        'delete',
        'options',
        'any',
    ],

    /**
     * The pattern for paths to be excluded from this package
     */
    'pattern' => '^.*$',

    /**
     * Define in which column the routes in json format should be stored
     */
    'routes_json_column' => 'routes_json'
];
