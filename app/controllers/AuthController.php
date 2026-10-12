<?php

declare(strict_types=1);

final class AuthController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function login(): void
    {
        if (!empty($_SESSION['auth_user'])) {
            redirect('index.php');
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        require __DIR__ . '/../views/auth/login.php';
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            renderError(405);
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            renderError(419);
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
            || $password === ''
        ) {
            $_SESSION['login_error'] =
                'Email atau password tidak valid.';
            redirect('index.php?page=login');
        }

        $userModel = new User($this->pdo);
        $user = $userModel->findByEmail($email);

        if (
            !$user
            || !password_verify($password, $user['password'])
        ) {
            $_SESSION['login_error'] =
                'Email atau password salah.';
            redirect('index.php?page=login');
        }

        session_regenerate_id(true);

        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        unset($_SESSION['login_error']);
        unset($_SESSION['_csrf_token']);

        redirect('index.php');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            renderError(405);
        }

        if (!verifyCsrfToken($_POST['_token'] ?? null)) {
            renderError(419);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();

        redirect('index.php?page=login');
    }
}
