<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$_ENV['BASE_PATH'] = dirname(__DIR__);

if (is_file(dirname(__DIR__) . '/.env')) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

$backupDirectory = base_path('storage/backups');

if (! is_dir($backupDirectory)) {
    mkdir($backupDirectory, 0750, true);
}

$filename = $backupDirectory . '/mysql-' . date('Ymd-His') . '.sql';
$command = [
    'mysqldump',
    '--host=' . env('DB_HOST', '127.0.0.1'),
    '--port=' . env('DB_PORT', '3306'),
    '--user=' . env('DB_USERNAME', 'root'),
    '--single-transaction',
    '--quick',
    '--routines',
    '--events',
    (string) env('DB_DATABASE', 'multi_hr'),
];

$password = (string) env('DB_PASSWORD', '');

if ($password !== '') {
    array_splice($command, 4, 0, ['--password=' . $password]);
}

$escaped = implode(' ', array_map('escapeshellarg', $command));
$process = proc_open($escaped, [
    1 => ['file', $filename, 'w'],
    2 => ['pipe', 'w'],
], $pipes);

if (! is_resource($process)) {
    fwrite(STDERR, 'Unable to start mysqldump.' . PHP_EOL);
    exit(1);
}

$error = stream_get_contents($pipes[2]);
$status = proc_close($process);

if ($status !== 0) {
    @unlink($filename);
    fwrite(STDERR, trim($error) . PHP_EOL);
    exit($status);
}

echo 'Database backup created: ' . $filename . PHP_EOL;
