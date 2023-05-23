<?php

namespace Svanthuijl\Routable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\App;

class Route extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'action',
        'controller',
        'locale',
        'method',
        'name',
        'path',
    ];

    /**
     * The call to execute the controller@action
     * @return mixed
     */
    public function call(): mixed
    {
        // Set locale
        if ($this->routable->locale)
            app()->setLocale($this->routable->locale);
        elseif ($this->locale)
            app()->setLocale($this->locale);

        // Call controller action
        return App::call($this->controller . '@' . $this->action, [
            'model' => $this->routable,
            'routeName' => $this->name,
        ]);
    }

    /**
     * The relationship with the routable model
     * @return MorphTo
     */
    public function routable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Return the generated route
     * @return string
     */
    public function getRouteAttribute(): string
    {
        return route('routable.' . $this->method, $this->path);
    }

    /**
     * Return route as string
     * @return string
     */
    public function __toString(): string
    {
        return $this->route;
    }
}
