<?php
declare(strict_types=1);

$code = (int) ($errorCode ?? 500);
$title = (string) ($errorTitle ?? 'Terjadi kesalahan');
$message = (string) ($errorMessage ?? 'Permintaan Anda belum dapat diproses.');
$icon = (string) ($errorIcon ?? 'bi-exclamation-triangle');
$backUrl = (string) ($errorBackUrl ?? 'index.php');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($code) ?> - <?= e($title) ?> | <?= e(APP_NAME) ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            color-scheme: light;
            --error-primary: #3457d5;
            --error-text: #172033;
            --error-muted: #64748b;
            --error-border: #e5eaf2;
            --error-surface: #ffffff;
            --error-background: #f5f7fb;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(circle at top left, #e9efff, transparent 38%),
                var(--error-background);
            color: var(--error-text);
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .error-card {
            width: 100%;
            max-width: 560px;
            padding: clamp(28px, 6vw, 52px);
            text-align: center;
            background: var(--error-surface);
            border: 1px solid var(--error-border);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgb(23 32 51 / 8%);
        }

        .error-icon {
            width: 88px;
            height: 88px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            border-radius: 24px;
            background: #edf2ff;
            color: var(--error-primary);
            font-size: 2.5rem;
        }

        .error-code {
            margin-bottom: 8px;
            color: var(--error-primary);
            font-size: clamp(3rem, 12vw, 5rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.06em;
        }

        .error-title {
            margin-bottom: 12px;
            font-size: clamp(1.4rem, 5vw, 1.8rem);
            font-weight: 700;
        }

        .error-message {
            max-width: 420px;
            margin: 0 auto 28px;
            color: var(--error-muted);
            line-height: 1.7;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .error-actions .btn {
            min-height: 44px;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 600;
        }

        .error-footer {
            margin-top: 28px;
            color: var(--error-muted);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <main class="error-card">
        <div class="error-icon" aria-hidden="true">
            <i class="bi <?= e($icon) ?>"></i>
        </div>

        <p class="error-code"><?= e($code) ?></p>
        <h1 class="error-title"><?= e($title) ?></h1>
        <p class="error-message"><?= e($message) ?></p>

        <div class="error-actions">
            <a class="btn btn-primary" href="<?= e($backUrl) ?>">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                <?= e($errorBackLabel ?? 'Kembali') ?>
            </a>
        </div>

        <p class="error-footer mb-0">
            <?= e(APP_NAME) ?> &middot; Employee Management System
        </p>
    </main>
</body>
</html>
