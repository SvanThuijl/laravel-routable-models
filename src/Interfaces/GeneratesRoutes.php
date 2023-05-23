<?php

namespace Svanthuijl\Routable\Interfaces;

use Illuminate\Support\Collection;

interface GeneratesRoutes
{
    public function forModel(HasRoutes $model): self;

    public function getAction(): string;
    public function getController(): string;
    public function getMethod(): string;
    public function getPaths(): Collection;
    public function getName(string|null $locale): string;
}