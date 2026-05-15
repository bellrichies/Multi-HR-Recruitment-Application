<?php

declare(strict_types=1);

use App\Core\Database;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$_ENV['BASE_PATH'] = dirname(__DIR__);

if (is_file(dirname(__DIR__) . '/.env')) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

$db = Database::connection();
$seederFiles = glob(dirname(__DIR__) . '/database/seeders/*.php') ?: [];
sort($seederFiles);

foreach ($seederFiles as $file) {
    $seeder = require $file;

    if (! is_callable($seeder)) {
        throw new RuntimeException('Seeder ' . basename($file) . ' must return a callable.');
    }

    $db->beginTransaction();

    try {
        $seeder($db);
        $db->commit();
        echo 'Seeded ' . basename($file) . PHP_EOL;
    } catch (Throwable $exception) {
        $db->rollBack();
        throw $exception;
    }
}
