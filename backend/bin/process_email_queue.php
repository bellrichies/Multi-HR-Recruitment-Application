<?php

declare(strict_types=1);

use App\Modules\Notifications\Services\NotificationService;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$_ENV['BASE_PATH'] = dirname(__DIR__);

if (is_file(dirname(__DIR__) . '/.env')) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

$container = new App\Core\Container();
$service = $container->get(NotificationService::class);
$result = $service->processEmailQueue((int) ($argv[1] ?? 25));

echo 'Email queue processed: sent=' . $result['sent'] . ' failed=' . $result['failed'] . PHP_EOL;
