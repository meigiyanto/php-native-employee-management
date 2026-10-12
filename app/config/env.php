<?php

declare(strict_types=1);

$envPath = dirname(__DIR__, 2) . '/.env';

if (!is_file($envPath) || !is_readable($envPath)) {
    throw new RuntimeException(
        'File .env tidak ditemukan atau tidak dapat dibaca.'
    );
}

$variables = parse_ini_file(
    $envPath,
    false,
    INI_SCANNER_RAW
);

if ($variables === false) {
    throw new RuntimeException('Gagal membaca file .env.');
}

foreach ($variables as $key => $value) {
    // Jangan menimpa environment yang sudah tersedia.
    if (getenv($key) === false) {
        putenv($key . '=' . (string) $value);
    }
}
