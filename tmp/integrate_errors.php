<?php

declare(strict_types=1);

$root = getenv('HOME') . '/employee-management';

$files = [
    'app/controllers/AuthController.php',
    'app/controllers/EmployeeController.php',
    'app/controllers/DepartmentController.php',
    'app/controllers/PositionController.php',
    'app/controllers/UserController.php',
];

$pattern = '/http_response_code\((403|404|405|419)\);'
    . '\s*exit\(\s*\'[^\']*\'\s*\);/';

foreach ($files as $relativePath) {
    $path = $root . '/' . $relativePath;

    if (!is_file($path)) {
        fwrite(STDERR, "File tidak ditemukan: {$relativePath}\n");
        continue;
    }

    $source = file_get_contents($path);

    if ($source === false) {
        fwrite(STDERR, "Gagal membaca: {$relativePath}\n");
        continue;
    }

    $count = 0;

    $updated = preg_replace_callback(
        $pattern,
        static function (array $matches): string {
            return 'renderError(' . $matches[1] . ');';
        },
        $source,
        -1,
        $count
    );

    if ($updated === null) {
        fwrite(STDERR, "Regex gagal: {$relativePath}\n");
        continue;
    }

    if ($count === 0) {
        echo "Tidak ada penggantian: {$relativePath}\n";
        continue;
    }

    $backup = $path . '.bak-error-integration';

    if (!copy($path, $backup)) {
        fwrite(STDERR, "Gagal membuat cadangan: {$relativePath}\n");
        continue;
    }

    if (file_put_contents($path, $updated) === false) {
        copy($backup, $path);
        fwrite(STDERR, "Gagal menulis: {$relativePath}\n");
        continue;
    }

    echo "{$relativePath}: {$count} blok diganti\n";
}
