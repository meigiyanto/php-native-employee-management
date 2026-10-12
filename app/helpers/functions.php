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

function renderError(
    int $statusCode,
    ?string $message = null
): never {
    $errors = [
        400 => [
            'title' => 'Permintaan tidak valid',
            'message' => 'Permintaan tidak dapat diproses. Periksa kembali data yang dikirim.',
            'icon' => 'bi-exclamation-circle',
        ],
        403 => [
            'title' => 'Akses ditolak',
            'message' => 'Anda tidak memiliki izin untuk mengakses halaman atau melakukan tindakan ini.',
            'icon' => 'bi-shield-lock',
        ],
        404 => [
            'title' => 'Halaman tidak ditemukan',
            'message' => 'Halaman atau data yang Anda cari tidak tersedia.',
            'icon' => 'bi-file-earmark-x',
        ],
        405 => [
            'title' => 'Metode tidak diizinkan',
            'message' => 'Metode permintaan ini tidak diizinkan untuk halaman tersebut.',
            'icon' => 'bi-signpost-split',
        ],
        419 => [
            'title' => 'Sesi atau token tidak valid',
            'message' => 'Permintaan ditolak karena token keamanan tidak valid atau sesi perlu diperbarui. Muat ulang halaman lalu coba lagi.',
            'icon' => 'bi-shield-exclamation',
        ],
        422 => [
            'title' => 'Data tidak dapat diproses',
            'message' => 'Data yang dikirim tidak memenuhi persyaratan.',
            'icon' => 'bi-clipboard-x',
        ],
        500 => [
            'title' => 'Terjadi kesalahan sistem',
            'message' => 'Sistem mengalami kendala saat memproses permintaan. Silakan coba kembali nanti.',
            'icon' => 'bi-exclamation-triangle',
        ],
    ];

    // Jangan izinkan status sembarang menghasilkan halaman error.
    if (!isset($errors[$statusCode])) {
        $statusCode = 500;
    }

    $errorCode = $statusCode;
    $errorTitle = $errors[$statusCode]['title'];
    $errorMessage = $message ?? $errors[$statusCode]['message'];
    $errorIcon = $errors[$statusCode]['icon'];
    $errorBackUrl = 'index.php';

    http_response_code($statusCode);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, private');

    require __DIR__ . '/../views/errors/show.php';
    exit;
}
