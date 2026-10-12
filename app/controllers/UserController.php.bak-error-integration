<?php

declare(strict_types=1);

final class UserController
{
    public function __construct(private PDO $pdo)
    {
    }

    private function model(): User
    {
        return new User($this->pdo);
    }

    private function requireAdmin(): void
    {
        if (($_SESSION['auth_user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Akses ditolak. Halaman ini hanya untuk admin.');
        }
    }

    private function verifyPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit('Metode permintaan tidak diizinkan.');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Token keamanan tidak valid. Muat ulang halaman.');
        }
    }

    private function redirectTo(string $page): never
    {
        redirect('index.php?page=' . $page);
    }

    private function flash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    private function validId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id !== false && $id !== null && $id > 0
            ? $id
            : null;
    }

    private function render(string $view, array $data = []): void
    {
        $titles = [
            'index' => 'Manajemen Pengguna',
            'create' => 'Tambah Pengguna',
            'edit' => 'Edit Pengguna',
        ];

        $pageTitle = $titles[$view] ?? 'Manajemen Pengguna';

        extract($data, EXTR_SKIP);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/users/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    private function validate(array $input, ?int $id = null): array
    {
        $errors = [];

        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $role = (string) ($input['role'] ?? '');
        $password = (string) ($input['password'] ?? '');

        if ($name === '' || mb_strlen($name) > 100) {
            $errors['name'] =
                'Nama wajib diisi dan maksimal 100 karakter.';
        }

        if (
            $email === ''
            || mb_strlen($email) > 150
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $errors['email'] = 'Masukkan alamat email yang valid.';
        } elseif ($this->model()->emailExists($email, $id)) {
            $errors['email'] = 'Email sudah digunakan akun lain.';
        }

        if (!in_array($role, ['admin', 'hr'], true)) {
            $errors['role'] = 'Pilih role yang valid.';
        }

        if ($id === null && strlen($password) < 8) {
            $errors['password'] =
                'Password wajib diisi, minimal 8 karakter.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $errors['password'] =
                'Password baru harus minimal 8 karakter.';
        }

        return $errors;
    }

    private function formData(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'role' => (string) ($input['role'] ?? 'hr'),
            'password' => (string) ($input['password'] ?? ''),
        ];
    }

    public function index(): void
    {
        $this->requireAdmin();

        $search = trim((string) ($_GET['search'] ?? ''));
        $perPage = 10;
        $model = $this->model();

        $totalUsers = $model->countAll($search);
        $totalPages = max(1, (int) ceil($totalUsers / $perPage));

        $requestedPage = filter_var(
            $_GET['p'] ?? 1,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $currentPage = $requestedPage === false
            ? 1
            : min($requestedPage, $totalPages);

        $offset = ($currentPage - 1) * $perPage;

        $this->render('index', [
            'users' => $model->all($search, $perPage, $offset),
            'search' => $search,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'startRecord' => $totalUsers > 0 ? $offset + 1 : 0,
            'endRecord' => min($offset + $perPage, $totalUsers),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->render('create', [
            'user' => [
                'name' => '',
                'email' => '',
                'role' => 'hr',
                'password' => '',
            ],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->verifyPost();

        $input = $this->formData($_POST);
        $errors = $this->validate($input);

        if ($errors) {
            $this->render('create', [
                'user' => $input,
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->create($input);
            $this->flash('Akun pengguna berhasil dibuat.');
            $this->redirectTo('users');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Akun gagal dibuat. Periksa kembali data pengguna.');
            $this->redirectTo('user-create');
        }
    }

    public function edit(): void
    {
        $this->requireAdmin();

        $id = $this->validId($_GET['id'] ?? null);
        $user = $id ? $this->model()->find($id) : null;

        if (!$user) {
            http_response_code(404);
            exit('Pengguna tidak ditemukan.');
        }

        $user['password'] = '';

        $this->render('edit', [
            'user' => $user,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        $this->verifyPost();

        $id = $this->validId($_POST['id'] ?? null);
        $existing = $id ? $this->model()->find($id) : null;

        if (!$existing) {
            http_response_code(404);
            exit('Pengguna tidak ditemukan.');
        }

        $input = $this->formData($_POST);
        $errors = $this->validate($input, $id);

        $currentUserId = (int) ($_SESSION['auth_user']['id'] ?? 0);

        if (
            $id === $currentUserId
            && $input['role'] !== 'admin'
        ) {
            $errors['role'] =
                'Kamu tidak dapat mengubah role akun admin yang sedang digunakan.';
        }

        if (
            $existing['role'] === 'admin'
            && $input['role'] !== 'admin'
            && $this->model()->countAdmins() <= 1
        ) {
            $errors['role'] =
                'Role admin terakhir tidak dapat diubah.';
        }

        if ($errors) {
            $this->render('edit', [
                'user' => array_merge($existing, $input),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->update($id, $input);
            $this->flash('Akun pengguna berhasil diperbarui.');
            $this->redirectTo('users');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Akun gagal diperbarui. Periksa kembali datanya.');
            redirect('index.php?page=user-edit&id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $this->verifyPost();

        $id = $this->validId($_POST['id'] ?? null);
        $user = $id ? $this->model()->find($id) : null;

        if (!$user) {
            $this->flash('Pengguna tidak ditemukan.');
            $this->redirectTo('users');
        }

        $currentUserId = (int) ($_SESSION['auth_user']['id'] ?? 0);

        if ($id === $currentUserId) {
            $this->flash('Kamu tidak dapat menghapus akun yang sedang digunakan.');
            $this->redirectTo('users');
        }

        if (
            $user['role'] === 'admin'
            && $this->model()->countAdmins() <= 1
        ) {
            $this->flash('Admin terakhir tidak dapat dihapus.');
            $this->redirectTo('users');
        }

        try {
            $this->model()->delete($id);
            $this->flash('Akun pengguna berhasil dihapus.');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Akun gagal dihapus.');
        }

        $this->redirectTo('users');
    }
}
