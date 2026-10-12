<?php

declare(strict_types=1);

final class DepartmentController
{
    public function __construct(private PDO $pdo)
    {
    }

    private function model(): Department
    {
        return new Department($this->pdo);
    }

    private function requireRole(array $roles): void
    {
        $role = $_SESSION['auth_user']['role'] ?? '';

        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            exit('Kamu tidak memiliki izin untuk melakukan tindakan ini.');
        }
    }

    private function render(string $view, array $data = []): void
    {
        $titles = [
            'index' => 'Data Departemen',
            'create' => 'Tambah Departemen',
            'show' => 'Detail Departemen',
            'edit' => 'Edit Departemen',
        ];

        $pageTitle = $titles[$view] ?? 'Data Departemen';

        extract($data, EXTR_SKIP);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/departments/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    private function flash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    private function redirectTo(string $page): never
    {
        redirect('index.php?page=' . $page);
    }

    private function validId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id !== false && $id !== null && $id > 0
            ? $id
            : null;
    }

    private function validate(array $input, ?int $id = null): array
    {
        $errors = [];
        $name = trim((string) ($input['name'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));

        if ($name === '' || mb_strlen($name) > 100) {
            $errors['name'] =
                'Nama departemen wajib diisi, maksimal 100 karakter.';
        } elseif ($this->model()->nameExists($name, $id)) {
            $errors['name'] = 'Nama departemen sudah digunakan.';
        }

        if ($description !== '' && mb_strlen($description) > 65535) {
            $errors['description'] = 'Deskripsi terlalu panjang.';
        }

        return $errors;
    }

    private function formData(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'description' => trim(
                (string) ($input['description'] ?? '')
            ),
        ];
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

    public function index(): void
    {
        $this->requireRole(['admin', 'hr']);

        $search = trim((string) ($_GET['search'] ?? ''));

        $this->render('index', [
            'departments' => $this->model()->all($search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['admin', 'hr']);

        $this->render('create', [
            'department' => ['name' => '', 'description' => ''],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $this->requireRole(['admin', 'hr']);
        $this->verifyPost();

        $input = $this->formData($_POST);
        $errors = $this->validate($input);

        if ($errors) {
            $this->render('create', [
                'department' => $input,
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->create($input);
            $this->flash('Departemen berhasil ditambahkan.');
            $this->redirectTo('departments');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash(
                'Departemen gagal disimpan. Periksa nama dan koneksi database.'
            );
            $this->redirectTo('department-create');
        }
    }

    public function show(): void
    {
        $this->requireRole(['admin', 'hr']);

        $id = $this->validId($_GET['id'] ?? null);
        $department = $id ? $this->model()->find($id) : null;

        if (!$department) {
            http_response_code(404);
            exit('Departemen tidak ditemukan.');
        }

        $this->render('show', ['department' => $department]);
    }

    public function edit(): void
    {
        $this->requireRole(['admin', 'hr']);

        $id = $this->validId($_GET['id'] ?? null);
        $department = $id ? $this->model()->find($id) : null;

        if (!$department) {
            http_response_code(404);
            exit('Departemen tidak ditemukan.');
        }

        $this->render('edit', [
            'department' => $department,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $this->requireRole(['admin', 'hr']);
        $this->verifyPost();

        $id = $this->validId($_POST['id'] ?? null);
        $existing = $id ? $this->model()->find($id) : null;

        if (!$existing) {
            http_response_code(404);
            exit('Departemen tidak ditemukan.');
        }

        $input = $this->formData($_POST);
        $errors = $this->validate($input, $id);

        if ($errors) {
            $this->render('edit', [
                'department' => array_merge($existing, $input),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->update($id, $input);
            $this->flash('Departemen berhasil diperbarui.');
            $this->redirectTo('departments');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Departemen gagal diperbarui.');
            redirect('index.php?page=department-edit&id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->requireRole(['admin']);
        $this->verifyPost();

        $id = $this->validId($_POST['id'] ?? null);
        $department = $id ? $this->model()->find($id) : null;

        if (!$department) {
            $this->flash('Departemen tidak ditemukan.');
            $this->redirectTo('departments');
        }

        if (
            (int) $department['employee_count'] > 0
            || (int) $department['position_count'] > 0
        ) {
            $this->flash(
                'Departemen tidak dapat dihapus karena masih memiliki karyawan atau jabatan.'
            );
            $this->redirectTo('departments');
        }

        try {
            $this->model()->delete($id);
            $this->flash('Departemen berhasil dihapus.');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash(
                'Departemen tidak dapat dihapus karena masih digunakan.'
            );
        }

        $this->redirectTo('departments');
    }
}
