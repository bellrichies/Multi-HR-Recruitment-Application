<?php

declare(strict_types=1);

$baseUrl = rtrim($argv[1] ?? (string) getenv('SMOKE_BASE_URL') ?: 'http://127.0.0.1:8080', '/');
$checks = [
    '/api/v1/health' => 200,
];

foreach ($checks as $path => $expectedStatus) {
    $context = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
    $body = file_get_contents($baseUrl . $path, false, $context);
    $statusLine = $http_response_header[0] ?? '';

    if (! str_contains($statusLine, (string) $expectedStatus) || $body === false) {
        fwrite(STDERR, "Smoke check failed for {$path}: {$statusLine}" . PHP_EOL);
        exit(1);
    }
}

echo 'Smoke tests passed for ' . $baseUrl . PHP_EOL;
