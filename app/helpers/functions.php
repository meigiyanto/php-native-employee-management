<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function csrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['_csrf_token'])
        && hash_equals($_SESSION['_csrf_token'], $token);
}

function rupiah(int|float|string|null $amount): string
{
    return 'Rp ' . number_format(
        (float) ($amount ?? 0),
        0,
        ',',
        '.'
    );
}

function statusLabel(string $status): string
{
    return match ($status) {
        'active' => 'Aktif',
        'inactive' => 'Tidak aktif',
        'on_leave' => 'Cuti',
        'resigned' => 'Mengundurkan diri',
        default => 'Tidak diketahui',
    };
}
