<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use Dotenv\Dotenv;

class Application
{
    private Container $container;
    private Router $router;

    public function __construct(private readonly string $basePath)
    {
        $_ENV['BASE_PATH'] = $this->basePath;
        $this->container = new Container();
        $this->router = new Router($this->container);
    }

    public function boot(): void
    {
        $this->loadEnvironment();
        date_default_timezone_set((string) config('app.timezone', 'UTC'));

        (new ErrorHandler())->register();

        $this->container->instance(Container::class, $this->container);
        $this->container->instance(Router::class, $this->router);
        $this->container->singleton(Request::class, fn () => new Request());
        $this->registerMiddlewareAliases();
        $this->loadRoutes();
    }

    public function run(): void
    {
        $this->router->dispatch($this->container->get(Request::class));
    }

    public function router(): Router
    {
        return $this->router;
    }

    private function loadEnvironment(): void
    {
        if (is_file($this->basePath . '/.env')) {
            Dotenv::createImmutable($this->basePath)->safeLoad();
        }
    }

    private function registerMiddlewareAliases(): void
    {
        $this->router->middlewareAlias('auth', AuthMiddleware::class);
        $this->router->middlewareAlias('csrf', CsrfMiddleware::class);
        $this->router->middlewareAlias('json', JsonMiddleware::class);
        $this->router->middlewareAlias('permission', PermissionMiddleware::class);
        $this->router->middlewareAlias('rate_limit', RateLimitMiddleware::class);
        $this->router->middlewareAlias('security_headers', SecurityHeadersMiddleware::class);
    }

    private function loadRoutes(): void
    {
        $router = $this->router;

        require $this->basePath . '/routes/api.php';
        require $this->basePath . '/routes/web.php';
    }
}
