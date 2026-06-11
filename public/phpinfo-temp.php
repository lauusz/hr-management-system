<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$expectedToken = (string) ($_ENV['PHPINFO_TOKEN'] ?? getenv('PHPINFO_TOKEN') ?: '');
$providedToken = (string) ($_GET['token'] ?? '');

if ($expectedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
    http_response_code(404);
    exit('Not Found');
}

phpinfo();
