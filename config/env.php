<?php
/**
 * Carga simple de variables de entorno desde .env
 * Compatible con PHP 8.5, sin dependencias externas.
 */

declare(strict_types=1);

function env_load(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $m)) {
            $value = $m[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $m)) {
            $value = $m[1];
        }
        if ($name !== '') {
            $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
    }
}

function env_val(string $key, mixed $default = null): mixed
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }
    $v = getenv($key);
    return $v !== false ? $v : $default;
}

env_load(__DIR__ . '/.env');
