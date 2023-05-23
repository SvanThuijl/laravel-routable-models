<?php

namespace Svanthuijl\Routable;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;
use Svanthuijl\Routable\Interfaces\HasRoutes;
use Svanthuijl\Routable\Interfaces\GeneratesRoutes;
use function collect;

class DefaultRouteGenerator implements GeneratesRoutes
{
    protected string $action;
    protected string $controller;
    protected string $fromProperty = 'slug';
    protected bool $isLocalized = false;
    protected string $localeProperty = 'locale';
    protected bool $isTranslatable = false;
    protected string $method = 'get';
    protected string|null $prefix = null;
    protected string|null $suffix = null;

    private string $currentLocale;
    private HasRoutes $model;

    public function __construct(public string $name = 'default')
    {

    }
    public function action(string $value): self
    {
        $this->action = $value;
        return $this;
    }
    public function controller(string $value): self
    {
        $this->controller = $value;
        return $this;
    }
    public function fromProperty(string $value): self
    {
        $this->fromProperty = $value;
        return $this;
    }
    public function isLocalized(bool $value = true, string|null $localeProperty = null): self
    {
        if ($value &&
            $this->isTranslatable)
            throw new \InvalidArgumentException('Routable model cannot be localized and translatable');

        if ($localeProperty !== null)
            $this->localeProperty = $localeProperty;

        $this->isLocalized = $value;
        return $this;
    }
    public function isTranslatable(bool $value = true): self
    {
        if ($value &&
            $this->isLocalized)
            throw new \InvalidArgumentException('Routable model cannot be localized and translatable');

        if ($value &&
            self::getLocales() === null)
            throw new \InvalidArgumentException('Routable model cannot be translatable due to missing configuration in routable-models');

        $this->isTranslatable = $value;
        return $this;
    }
    public function method(string $value): self
    {
        if (!in_array($value, config('routable-models.methods')))
            throw new \InvalidArgumentException('Method "' . $value . '" is not supported due to routable-models configuration');

        $this->method = $value;
        return $this;
    }
    public function prefix(string $value): self
    {
        $this->prefix = $value;
        return $this;
    }
    public function suffix(string $value): self
    {
        $this->suffix = $value;
        return $this;
    }

    public function forModel(HasRoutes $model): self
    {
        $this->model = $model;

        $this->validateRouteForModel();

        return $this;
    }

    private function validateRouteForModel(): void
    {
        $this->validateFromProperty()
            ->validateIsLocalized()
            ->validateIsTranslatable()
            ->validateMethod()
            ->validatePrefix()
            ->validateSuffix();
    }
    private function validateFromProperty(): self
    {
        if (!isset($this->model->{$this->fromProperty}))
            throw new \InvalidArgumentException('"' . $this->fromProperty . '" does not exists on "' . $this->model::class . '"');

        return $this;
    }
    private function validateIsLocalized(): self
    {
        if (!$this->isLocalized)
            return $this;

        if (!isset($this->model->{$this->localeProperty}))
            throw new \InvalidArgumentException('"' . $this->localeProperty . '" does not exists on "' . $this->model::class . '"');

        if ($this->model->{$this->localeProperty} === null ||
            $this->model->{$this->localeProperty} === '')
            throw new \InvalidArgumentException('"' . $this->localeProperty . '" is empty on "' . $this->model::class . '"');

        return $this;
    }
    private function validateIsTranslatable(): self
    {
        if (!$this->isTranslatable)
            return $this;

        if (!in_array(HasTranslations::class, class_uses_recursive($this->model::class)))
            throw new \InvalidArgumentException('"' . $this->model::class . '" does not use "' . HasTranslations::class . '"');

        if (!in_array($this->fromProperty, $this->model->translatable))
            throw new \InvalidArgumentException('"' . $this->fromProperty . '" is not configured to be translatable in "' . $this->model::class . '"');

        return $this;
    }
    private function validateMethod(): self
    {
        return $this;
    }
    private function validatePrefix(): self
    {
        return $this;
    }
    private function validateSuffix(): self
    {
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }
    public function getController(): string
    {
        return $this->controller;
    }
    public function getFromProperty(): string
    {
        return $this->fromProperty;
    }
    public function getIsLocalized(): bool
    {
        return $this->isLocalized;
    }
    public function getIsTranslatable(): bool
    {
        return $this->isTranslatable;
    }
    public function getLocaleProperty(): bool
    {
        return $this->localeProperty;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function getName(string|null $locale = null): string
    {
        if ($locale &&
            $this->isTranslatable)
            return $this->name . '.' . $locale;
        return $this->name;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    private function setCurrentLocale($locale): self
    {
        $this->currentLocale = $locale;
        return $this;
    }

    public function getPaths(): Collection
    {
        if (!$this->isTranslatable)
            return collect([$this->getPath() => null]);

        return collect(self::getLocales())
            ->mapWithKeys(
                fn ($locale) =>
                [
                    $this->setCurrentLocale($locale)
                        ->getPath()
                    => $locale
                ]
            );
    }
    private function getPath(): string
    {
        return collect()
            ->add($this->addPathLocale())
            ->add($this->addPathPrefix())
            ->add($this->addPathFromProperty())
            ->add($this->addPathSuffix())
            ->whereNotNull()
            ->implode('/');
    }
    private function addPathLocale(): string|null
    {
        if ($this->isLocalized)
            return $this->model->{$this->localeProperty};

        if ($this->isTranslatable)
            return $this->currentLocale;

        return null;
    }
    private function addPathPrefix(): string|null
    {
        if ($this->prefix)
            return $this->prefix;

        return null;
    }
    private function addPathFromProperty(): string
    {
        if ($this->isTranslatable)
            return $this->model->getTranslation($this->fromProperty, $this->currentLocale);

        return $this->model->{$this->fromProperty};
    }
    private function addPathSuffix(): string|null
    {
        if ($this->suffix)
            return $this->suffix;

        return null;
    }

    private static function getLocales(): Collection|null
    {
        $localesStr = config('routable-models.locales');
        if ($localesStr === null ||
            $localesStr === '')
            return null;

        return str($localesStr)->explode(',');
    }
}