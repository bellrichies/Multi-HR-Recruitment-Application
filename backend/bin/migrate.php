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
$migrationFiles = glob(dirname(__DIR__) . '/database/migrations/*.php') ?: [];
sort($migrationFiles);

$db->exec('CREATE TABLE IF NOT EXISTS migrations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$executed = $db->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

foreach ($migrationFiles as $file) {
    $migration = basename($file);

    if (in_array($migration, $executed, true)) {
        echo "Skipped {$migration}" . PHP_EOL;
        continue;
    }

    $statements = require $file;

    if (! is_array($statements)) {
        throw new RuntimeException("Migration {$migration} must return an array of SQL statements.");
    }

    try {
        foreach ($statements as $statement) {
            $db->exec($statement);
        }

        $record = $db->prepare('INSERT INTO migrations (migration, executed_at) VALUES (:migration, NOW())');
        $record->execute(['migration' => $migration]);
        echo "Migrated {$migration}" . PHP_EOL;
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        throw $exception;
    }
}
