<?php

declare(strict_types=1);

namespace App\Core;

class Route
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly mixed $handler,
        private array $middleware = []
    ) {
    }

    public function middleware(array|string $middleware): self
    {
        $items = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $items);

        return $this;
    }

    public function middlewareStack(): array
    {
        return $this->middleware;
    }
}
