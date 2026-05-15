<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private ?array $json = null;
    private array $attributes = [];

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($_POST['_method'])) {
            return strtoupper((string) $_POST['_method']);
        }

        return $method;
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        if ($scriptName !== '/' && str_starts_with($path, $scriptName)) {
            $path = substr($path, strlen($scriptName)) ?: '/';
        }

        return '/' . trim($path, '/');
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $_GET : ($_GET[$key] ?? $default);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $input = array_merge($_POST, $this->json());

        return $key === null ? $input : ($input[$key] ?? $default);
    }

    public function all(): array
    {
        return $this->input();
    }

    public function files(?string $key = null): mixed
    {
        return $key === null ? $_FILES : ($_FILES[$key] ?? null);
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

        return $_SERVER[$normalized] ?? $_SERVER[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');

        if (! is_string($header) || ! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return trim(substr($header, 7));
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function userAgent(): string
    {
        return (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function user(): ?array
    {
        $user = $this->attribute('user');

        return is_array($user) ? $user : null;
    }

    private function json(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $contentType = $this->header('Content-Type', '');

        if (! is_string($contentType) || ! str_contains(strtolower($contentType), 'application/json')) {
            return $this->json = [];
        }

        $raw = file_get_contents('php://input') ?: '';
        $decoded = $raw === '' ? [] : json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw new HttpException('Invalid JSON payload.', 400);
        }

        return $this->json = $decoded;
    }
}
