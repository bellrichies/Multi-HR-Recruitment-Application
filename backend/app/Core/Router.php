<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewareAliases = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $path, mixed $handler): Route
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): Route
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): Route
    {
        return $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, mixed $handler): Route
    {
        return $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, mixed $handler): Route
    {
        return $this->add('DELETE', $path, $handler);
    }

    public function middlewareAlias(string $alias, string $class): void
    {
        $this->middlewareAliases[$alias] = $class;
    }

    public function dispatch(Request $request): void
    {
        $route = $this->match($request);

        if ($route === null) {
            throw new HttpException('Route not found.', 404);
        }

        foreach ($route->middlewareStack() as $middleware) {
            $this->runMiddleware((string) $middleware, $request);
        }

        $this->execute($route->handler, $request, $this->routeParameters($route->path, $request->path()));
    }

    private function add(string $method, string $path, mixed $handler): Route
    {
        $path = '/' . trim($path, '/');
        $route = new Route($method, $path, $handler);
        $this->routes[$method][] = $route;

        return $route;
    }

    private function match(Request $request): ?Route
    {
        foreach ($this->routes[$request->method()] ?? [] as $route) {
            if ($this->matches($route->path, $request->path())) {
                return $route;
            }
        }

        return null;
    }

    private function matches(string $routePath, string $requestPath): bool
    {
        $pattern = preg_replace('/\{[A-Za-z_][A-Za-z0-9_]*}/', '([^/]+)', $routePath);

        return (bool) preg_match('#^' . $pattern . '$#', $requestPath);
    }

    private function routeParameters(string $routePath, string $requestPath): array
    {
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)}/', $routePath, $names);
        $pattern = preg_replace('/\{[A-Za-z_][A-Za-z0-9_]*}/', '([^/]+)', $routePath);
        preg_match('#^' . $pattern . '$#', $requestPath, $values);
        array_shift($values);

        return array_combine($names[1], $values) ?: [];
    }

    private function runMiddleware(string $middleware, Request $request): void
    {
        [$name, $parameter] = array_pad(explode(':', $middleware, 2), 2, null);
        $class = $this->middlewareAliases[$name] ?? $name;
        $instance = $this->container->get($class);

        if (! method_exists($instance, 'handle')) {
            throw new HttpException("Middleware {$class} must define a handle method.", 500);
        }

        $instance->handle($request, $parameter);
    }

    private function execute(mixed $handler, Request $request, array $parameters): void
    {
        if (is_callable($handler)) {
            $handler($request, ...array_values($parameters));
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = $this->container->get($class);
            $controller->{$method}($request, ...array_values($parameters));
            return;
        }

        throw new HttpException('Route handler is invalid.', 500);
    }
}
