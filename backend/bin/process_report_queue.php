<?php

declare(strict_types=1);

use App\Support\Logger;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$_ENV['BASE_PATH'] = dirname(__DIR__);

if (is_file(dirname(__DIR__) . '/.env')) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

(new Logger())->info('Report queue worker heartbeat.', [
    'driver' => env('QUEUE_DRIVER', 'database'),
    'batch_size' => (int) ($argv[1] ?? 10),
]);

echo 'Report queue worker completed heartbeat.' . PHP_EOL;
