<?php

declare(strict_types=1);

final class PositionController
{
    public function __construct(private PDO $pdo)
    {
    }

    private function model(): Position
    {
        return new Position($this->pdo);
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
            'index' => 'Data Jabatan',
            'create' => 'Tambah Jabatan',
            'show' => 'Detail Jabatan',
            'edit' => 'Edit Jabatan',
        ];

        $pageTitle = $titles[$view] ?? 'Data Jabatan';

        extract($data, EXTR_SKIP);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/positions/' . $view . '.php';
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

    private function departments(): array
    {
        return $this->pdo
            ->query('SELECT id, name FROM departments ORDER BY name ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    private function formData(array $input): array
    {
        return [
            'department_id' => trim(
                (string) ($input['department_id'] ?? '')
            ),
            'name' => trim((string) ($input['name'] ?? '')),
            'description' => trim(
                (string) ($input['description'] ?? '')
            ),
        ];
    }

    private function validate(
        array $input,
        ?int $id = null,
        ?array $existing = null
    ): array {
        $errors = [];

        $departmentId = filter_var(
            $input['department_id'] ?? null,
            FILTER_VALIDATE_INT
        );
        $name = trim((string) ($input['name'] ?? ''));
        $description = trim(
            (string) ($input['description'] ?? '')
        );

        if (
            $departmentId === false
            || $departmentId === null
            || $departmentId <= 0
            || !$this->model()->departmentExists((int) $departmentId)
        ) {
            $errors['department_id'] = 'Pilih departemen yang valid.';
        }

        if ($name === '' || mb_strlen($name) > 100) {
            $errors['name'] =
                'Nama jabatan wajib diisi, maksimal 100 karakter.';
        } elseif (
            $departmentId !== false
            && $departmentId !== null
            && $departmentId > 0
            && $this->model()->departmentExists((int) $departmentId)
            && $this->model()->nameExists(
                (int) $departmentId,
                $name,
                $id
            )
        ) {
            $errors['name'] =
                'Nama jabatan sudah digunakan di departemen ini.';
        }

        if ($description !== '' && mb_strlen($description) > 65535) {
            $errors['description'] = 'Deskripsi terlalu panjang.';
        }

        // Jabatan yang sedang digunakan karyawan tidak boleh dipindahkan
        // ke departemen lain karena akan merusak relasi karyawan.
        if (
            $existing !== null
            && (int) $existing['employee_count'] > 0
            && $departmentId !== false
            && $departmentId !== null
            && (int) $departmentId !== (int) $existing['department_id']
        ) {
            $errors['department_id'] =
                'Departemen tidak dapat diubah karena jabatan masih digunakan karyawan.';
        }

        return $errors;
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

        $model = $this->model();
        $perPage = 10;
        $totalPositions = $model->countAll($search);
        $totalPages = max(
            1,
            (int) ceil($totalPositions / $perPage)
        );

        $requestedPage = filter_var(
            $_GET['p'] ?? 1,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $currentPage = $requestedPage === false
            ? 1
            : min($requestedPage, $totalPages);

        $offset = ($currentPage - 1) * $perPage;

        $positions = $model->all(
            $search,
            $perPage,
            $offset
        );

        $this->render('index', [
            'positions' => $positions,
            'search' => $search,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'totalPositions' => $totalPositions,
            'totalPages' => $totalPages,
            'startRecord' => $totalPositions > 0 ? $offset + 1 : 0,
            'endRecord' => min(
                $offset + $perPage,
                $totalPositions
            ),
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['admin', 'hr']);

        $this->render('create', [
            'position' => [
                'department_id' => '',
                'name' => '',
                'description' => '',
            ],
            'departments' => $this->departments(),
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
                'position' => $input,
                'departments' => $this->departments(),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->create([
                'department_id' => (int) $input['department_id'],
                'name' => $input['name'],
                'description' => $input['description'] !== ''
                    ? $input['description']
                    : null,
            ]);

            $this->flash('Jabatan berhasil ditambahkan.');
            $this->redirectTo('positions');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());

            $this->flash(
                'Jabatan gagal disimpan. Periksa data dan koneksi database.'
            );
            $this->redirectTo('position-create');
        }
    }

    public function show(): void
    {
        $this->requireRole(['admin', 'hr']);

        $id = $this->validId($_GET['id'] ?? null);
        $position = $id ? $this->model()->find($id) : null;

        if (!$position) {
            http_response_code(404);
            exit('Jabatan tidak ditemukan.');
        }

        $this->render('show', ['position' => $position]);
    }

    public function edit(): void
    {
        $this->requireRole(['admin', 'hr']);

        $id = $this->validId($_GET['id'] ?? null);
        $position = $id ? $this->model()->find($id) : null;

        if (!$position) {
            http_response_code(404);
            exit('Jabatan tidak ditemukan.');
        }

        $this->render('edit', [
            'position' => $position,
            'departments' => $this->departments(),
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
            exit('Jabatan tidak ditemukan.');
        }

        $input = $this->formData($_POST);
        $errors = $this->validate($input, $id, $existing);

        if ($errors) {
            $this->render('edit', [
                'position' => array_merge($existing, $input),
                'departments' => $this->departments(),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->update($id, [
                'department_id' => (int) $input['department_id'],
                'name' => $input['name'],
                'description' => $input['description'] !== ''
                    ? $input['description']
                    : null,
            ]);

            $this->flash('Jabatan berhasil diperbarui.');
            $this->redirectTo('positions');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Jabatan gagal diperbarui.');
            redirect('index.php?page=position-edit&id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->requireRole(['admin']);
        $this->verifyPost();

        $id = $this->validId($_POST['id'] ?? null);
        $position = $id ? $this->model()->find($id) : null;

        if (!$position) {
            $this->flash('Jabatan tidak ditemukan.');
            $this->redirectTo('positions');
        }

        if ((int) $position['employee_count'] > 0) {
            $this->flash(
                'Jabatan tidak dapat dihapus karena masih digunakan karyawan.'
            );
            $this->redirectTo('positions');
        }

        try {
            $this->model()->delete($id);
            $this->flash('Jabatan berhasil dihapus.');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash(
                'Jabatan tidak dapat dihapus karena masih memiliki relasi data.'
            );
        }

        $this->redirectTo('positions');
    }
}
