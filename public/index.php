<?php

declare(strict_types=1);

use PoultryTrak\Support\Http\Application;

ini_set('display_errors', '0');
$method = is_string($_SERVER['REQUEST_METHOD'] ?? null) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$uri = is_string($_SERVER['REQUEST_URI'] ?? null) ? $_SERVER['REQUEST_URI'] : '/';

try {
    /** @var Application $application */
    $application = require dirname(__DIR__) . '/config/bootstrap.php';
    $application->handle($method, $uri)->send($method === 'HEAD');
} catch (Throwable $exception) {
    // Never emit exception messages, paths, credentials or a stack trace to clients/logs.
    $requestId = bin2hex(random_bytes(16));
    error_log(json_encode([
        'event' => 'request.failed',
        'request_id' => $requestId,
        'exception_class' => get_class($exception),
    ], JSON_THROW_ON_ERROR));
    http_response_code(503);
    header('Content-Type: application/problem+json');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Request-ID: ' . $requestId);
    if ($method !== 'HEAD') {
        echo json_encode([
            'type' => 'about:blank',
            'title' => 'Service Unavailable',
            'status' => 503,
            'detail' => 'The service is temporarily unavailable.',
            'request_id' => $requestId,
        ], JSON_THROW_ON_ERROR);
    }
}
