<?php

declare(strict_types=1);

namespace App\Support;

use Throwable;

class Logger
{
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    public function exception(Throwable $exception, array $context = []): void
    {
        $this->error($exception->getMessage(), array_merge($context, [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]));
    }

    private function write(string $level, string $message, array $context = []): void
    {
        $directory = base_path('storage/logs');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $record = [
            'timestamp' => date(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        file_put_contents(
            $directory . '/app-' . date('Y-m-d') . '.log',
            json_encode($record, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
