<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'SCM\\';
    $len = strlen($prefix);

    if (strncmp($class, $prefix, $len) !== 0) {
        return;
    }

    $relative = substr($class, $len);
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
