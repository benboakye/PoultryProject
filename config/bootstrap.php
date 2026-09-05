<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use PoultryTrak\Support\Config;
use PoultryTrak\Support\Http\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('UTC');
Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
$values = [];
foreach (['APP_ENV', 'APP_URL', 'APP_DEBUG'] as $key) {
    $processValue = getenv($key);
    $value = $processValue !== false ? $processValue : ($_ENV[$key] ?? $_SERVER[$key] ?? null);
    if (is_string($value)) {
        $values[$key] = $value;
    }
}

return new Application(Config::fromArray($values));
