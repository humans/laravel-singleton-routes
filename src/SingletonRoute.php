<?php

namespace Humans\SingletonRoutes;

use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * @method self middleware(array | string $middleware)
 */
class SingletonRoute
{
    use ForwardsCalls;

    protected const SUPPORTED_ACTIONS = [
        'show', 'create', 'store', 'edit', 'update', 'destroy',
    ];

    protected array $options = [];

    protected string $name;

    protected string $controller;

    protected string $path;

    protected RouteRegistrar $registrar;

    public function __construct(string $name, string $controller)
    {
        $this->registrar = App::make(RouteRegistrar::class);
        $this->controller = $controller;
        $this->name = $name;
    }

    public function only(array|string $actions): self
    {
        $this->options['only'] = is_array($actions) ? $actions : func_get_args();

        return $this;
    }

    public function except(array|string $actions): self
    {
        $this->options['except'] = is_array($actions) ? $actions : func_get_args();

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    protected function getResource(): string
    {
        if (! $this->isNestedResource()) {
            return $this->name;
        }

        return Str::of($this->name)->after('.');
    }

    protected function getPrefix(): string
    {
        if (! $this->isNestedResource()) {
            return '';
        }

        return sprintf(
            '%s/{%s}',
            $parent = Str::of($this->name)->before('.'),
            $parent->singular(),
        );
    }

    protected function getPath(): string
    {
        return $this->path ??= Str::after($this->name, '.');
    }

    protected function getActions(): array
    {
        if (array_key_exists('only', $this->options)) {
            return $this->options['only'];
        }

        if (array_key_exists('except', $this->options)) {
            return array_diff(self::SUPPORTED_ACTIONS, $this->options['except']);
        }

        return self::SUPPORTED_ACTIONS;
    }

    protected function getRoutes(): array
    {
        return collect($this->defaultRoutes())
            ->only($this->getActions())
            ->toArray();
    }

    protected function isNestedResource(): bool
    {
        return str_contains($this->name, '.');
    }

    protected function defaultRoutes(): array
    {
        return [
            'show' => fn () => Route::get("{$this->getPath()}", 'show')->name('.show'),
            'create' => fn () => Route::get("{$this->getPath()}/new", 'create')->name('.create'),
            'store' => fn () => Route::post("{$this->getPath()}", 'store')->name('.store'),
            'edit' => fn () => Route::get("{$this->getPath()}/edit", 'edit')->name('.edit'),
            'update' => fn () => Route::match(['PUT', 'PATCH'], "{$this->getPath()}", 'update')->name('.update'),
            'destroy' => fn () => Route::delete("{$this->getPath()}", 'destroy')->name('.destroy'),
        ];
    }

    public function register(): void
    {
        $this->registrar
            ->controller($this->controller)
            ->prefix($this->getPrefix())
            ->as($this->name)
            ->group(function () {
                collect($this->getRoutes())->each->__invoke();
            });
    }

    public function __call(string $name, array $parameters = []): self
    {
        $this->forwardCallTo($this->registrar, $name, $parameters);

        return $this;
    }

    public function __destruct()
    {
        $this->register();
    }
}
