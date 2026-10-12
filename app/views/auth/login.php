<?php
    declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= e(APP_NAME) ?></title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="card border-0 shadow-sm w-100" style="max-width: 420px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-people-fill text-primary fs-1"></i>
                <h1 class="h4 fw-bold mt-2">Employee Management</h1>
                <p class="text-secondary mb-0">
                    Masuk ke panel administrasi
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=login-submit"
                  method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrfToken()) ?>"
                >

                <div class="mb-3">
                    <label for="email" class="form-label">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        maxlength="150"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
