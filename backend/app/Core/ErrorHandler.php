<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\Logger;
use Throwable;

class ErrorHandler
{
    public function register(): void
    {
        error_reporting(E_ALL);

        set_exception_handler(fn (Throwable $exception) => $this->handleException($exception));
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }

    public function handleException(Throwable $exception): void
    {
        $debug = (bool) config('app.debug', false);

        if ($exception instanceof ValidationException) {
            Response::validation($exception->errors(), $exception->getMessage());
            return;
        }

        if ($exception instanceof HttpException) {
            Response::error($exception->getMessage(), $exception->errors(), $exception->statusCode());
            return;
        }

        (new Logger())->exception($exception);

        Response::error('An unexpected server error occurred.', $debug ? [
            'exception' => [
                $exception->getMessage(),
                $exception->getFile() . ':' . $exception->getLine(),
            ],
        ] : [], 500);
    }

}
