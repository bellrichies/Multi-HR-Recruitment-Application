<?php

declare(strict_types=1);

if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $basePath = $_ENV['BASE_PATH'] ?? dirname(__DIR__, 2);

        return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);
        $path = base_path('app/Config/' . $file . '.php');

        if (! is_file($path)) {
            return $default;
        }

        $value = require $path;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
