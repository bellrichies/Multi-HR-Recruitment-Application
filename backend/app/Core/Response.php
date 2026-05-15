<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function success(
        mixed $data = [],
        string $message = 'Request completed successfully.',
        array $meta = [],
        int $status = 200
    ): void {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => self::objectWhenEmpty($data),
            'meta' => self::objectWhenEmpty($meta),
        ], $status);
    }

    public static function error(
        string $message = 'An error occurred.',
        array $errors = [],
        int $status = 500
    ): void {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => self::objectWhenEmpty($errors),
        ], $status);
    }

    public static function validation(array $errors, string $message = 'Validation failed.'): void
    {
        self::error($message, $errors, 422);
    }

    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    private static function objectWhenEmpty(mixed $value): mixed
    {
        if (is_array($value) && $value === []) {
            return (object) [];
        }

        return $value;
    }
}
