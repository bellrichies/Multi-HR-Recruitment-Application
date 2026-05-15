<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function success(
        mixed $data = [],
        string $message = 'Request completed successfully.',
        array $meta = [],
        int $status = 200
    ): void {
        Response::success($data, $message, $meta, $status);
    }

    protected function error(string $message = 'An error occurred.', array $errors = [], int $status = 500): void
    {
        Response::error($message, $errors, $status);
    }
}
