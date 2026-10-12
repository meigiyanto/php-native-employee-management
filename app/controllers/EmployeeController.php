<?php

declare(strict_types=1);

final class EmployeeController
{
    public function __construct(private PDO $pdo)
    {
    }

    private function model(): Employee
    {
        return new Employee($this->pdo);
    }

    private function render(string $view, array $data = []): void
    {
        $titles = [
            'index' => 'Data Karyawan',
            'create' => 'Tambah Karyawan',
            'edit' => 'Edit Karyawan',
            'show' => 'Detail Karyawan',
        ];

        $pageTitle = $titles[$view] ?? 'Data Karyawan';

        extract($data, EXTR_SKIP);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/employees/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    private function redirectTo(string $page): never
    {
        redirect('index.php?page=' . $page);
    }

    private function flash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    private function departments(): array
    {
        return $this->pdo
            ->query('SELECT id, name FROM departments ORDER BY name')
            ->fetchAll();
    }

    private function positions(): array
    {
        return $this->pdo
            ->query(
                'SELECT id, department_id, name
                 FROM positions ORDER BY name'
            )
            ->fetchAll();
    }

    public function index(): void
    {
        $search = trim((string) ($_GET['search'] ?? ''));

        $departmentId = filter_var(
            $_GET['department_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $positionId = filter_var(
            $_GET['position_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $allowedStatuses = [
            'active',
            'inactive',
            'on_leave',
            'resigned',
        ];

        $status = (string) ($_GET['employment_status'] ?? '');

        $filters = [
            'department_id' => (
                $departmentId !== false
                && $departmentId !== null
                && $departmentId > 0
            ) ? $departmentId : null,

            'position_id' => (
                $positionId !== false
                && $positionId !== null
                && $positionId > 0
            ) ? $positionId : null,

            'employment_status' => in_array(
                $status,
                $allowedStatuses,
                true
            ) ? $status : '',
        ];

        $departments = $this->departments();

        $positions = $this->pdo->query(
            'SELECT p.id, p.name, p.department_id,
                    d.name AS department_name
             FROM positions p
             JOIN departments d ON d.id = p.department_id
             ORDER BY d.name, p.name'
        )->fetchAll();

        $model = $this->model();

        $perPage = 10;
        $totalEmployees = $model->countAll($search, $filters);
        $totalPages = max(1, (int) ceil($totalEmployees / $perPage));

        $requestedPage = filter_var(
            $_GET['p'] ?? 1,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $currentPage = $requestedPage === false
            ? 1
            : min($requestedPage, $totalPages);

        $offset = ($currentPage - 1) * $perPage;

        $employees = $model->all(
            $search,
            $filters,
            $perPage,
            $offset
        );

        $this->render('index', [
            'employees' => $employees,
            'search' => $search,
            'filters' => $filters,
            'departments' => $departments,
            'positions' => $positions,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'totalEmployees' => $totalEmployees,
            'totalPages' => $totalPages,
            'startRecord' => $totalEmployees > 0 ? $offset + 1 : 0,
            'endRecord' => min($offset + $perPage, $totalEmployees),
        ]);

    }

    public function create(): void
    {
        $this->render('create', [
            'employee' => [],
            'departments' => $this->departments(),
            'positions' => $this->positions(),
            'errors' => [],
        ]);
    }

    private function validate(array $input, ?int $id = null): array
    {
        $errors = [];

        $employeeCode = trim((string) ($input['employee_code'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $gender = (string) ($input['gender'] ?? '');
        $birthDate = trim((string) ($input['birth_date'] ?? ''));
        $hireDate = trim((string) ($input['hire_date'] ?? ''));
        $departmentId = filter_var(
            $input['department_id'] ?? null,
            FILTER_VALIDATE_INT
        );
        $positionId = filter_var(
            $input['position_id'] ?? null,
            FILTER_VALIDATE_INT
        );
        $status = (string) ($input['employment_status'] ?? 'active');
        $salaryInput = trim((string) ($input['salary'] ?? '0'));
        $address = trim((string) ($input['address'] ?? ''));

        if ($fullName === '' || mb_strlen($fullName) > 150) {
            $errors['full_name'] = 'Nama wajib diisi, maksimal 150 karakter.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 150) {
            $errors['email'] = 'Format email tidak valid.';
        }

        if (strlen($phone) > 25) {
            $errors['phone'] = 'Nomor telepon maksimal 25 karakter.';
        }

        if ($gender !== '' && !in_array(
            $gender,
            ['male', 'female', 'other'],
            true
        )) {
            $errors['gender'] = 'Pilihan jenis kelamin tidak valid.';
        }

        foreach (['birth_date' => $birthDate, 'hire_date' => $hireDate] as $field => $date) {
            if ($date !== '') {
                $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

                if (!$parsed || $parsed->format('Y-m-d') !== $date) {
                    $errors[$field] = 'Format tanggal tidak valid.';
                } elseif ($field === 'birth_date'
                    && $date > date('Y-m-d')) {
                    $errors[$field] = 'Tanggal lahir tidak boleh di masa depan.';
                }
            }
        }

        if ($hireDate === '') {
            $errors['hire_date'] = 'Tanggal masuk wajib diisi.';
        }

        if (!$departmentId || !$positionId) {
            $errors['department_id'] = 'Departemen dan jabatan wajib dipilih.';
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM positions
                 WHERE id = :position_id
                   AND department_id = :department_id'
            );
            $stmt->execute([
                'position_id' => $positionId,
                'department_id' => $departmentId,
            ]);

            if ((int) $stmt->fetchColumn() === 0) {
                $errors['position_id'] =
                    'Jabatan tidak sesuai dengan departemen.';
            }
        }

        if (!in_array(
            $status,
            ['active', 'inactive', 'on_leave', 'resigned'],
            true
        )) {
            $errors['employment_status'] = 'Status karyawan tidak valid.';
        }

        if (
            $salaryInput === ''
            || !is_numeric($salaryInput)
            || !is_finite((float) $salaryInput)
            || (float) $salaryInput < 0
            || (float) $salaryInput > 9999999999999.99
            || !preg_match('/^\d{1,13}(\.\d{1,2})?$/', $salaryInput)
        ) {
            $errors['salary'] = 'Gaji harus berupa angka positif dengan maksimal dua desimal.';
        }

        if (mb_strlen($address) > 65535) {
            $errors['address'] = 'Alamat terlalu panjang.';
        }

        $sql = 'SELECT COUNT(*) FROM employees
                WHERE (employee_code = :code OR email = :email)';
        $params = ['code' => $employeeCode, 'email' => $email];

        if ($id !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ((int) $stmt->fetchColumn() > 0) {
            $errors['duplicate'] = 'Kode karyawan atau email sudah digunakan.';
        }

        return $errors;
    }

    private function formData(array $input): array
    {
        return [
            'full_name' => trim((string) ($input['full_name'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'gender' => ($input['gender'] ?? '') ?: null,
            'birth_date' => ($input['birth_date'] ?? '') ?: null,
            'hire_date' => trim((string) ($input['hire_date'] ?? '')),
            'department_id' => (int) ($input['department_id'] ?? 0),
            'position_id' => (int) ($input['position_id'] ?? 0),
            'employment_status' => (string) ($input['employment_status'] ?? 'active'),
            'salary' => trim((string) ($input['salary'] ?? '0')),
            'address' => trim((string) ($input['address'] ?? '')),
        ];
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan.');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
        }

        $input = $this->formData($_POST);
        $errors = $this->validate($_POST);

        if ($errors) {
            $this->render('create', [
                'employee' => $input,
                'departments' => $this->departments(),
                'positions' => $this->positions(),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->create($input);
            $this->flash('Data karyawan berhasil ditambahkan.');
            $this->redirectTo('employees');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Data gagal disimpan. Periksa kembali kode dan email.');
            $this->redirectTo('employee-create');
        }
    }

    public function show(): void
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $employee = $id ? $this->model()->find($id) : null;

        if (!$employee) {
            http_response_code(404);
            exit('Data karyawan tidak ditemukan.');
        }

        $this->render('show', ['employee' => $employee]);
    }

    public function edit(): void
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $employee = $id ? $this->model()->find($id) : null;

        if (!$employee) {
            http_response_code(404);
            exit('Data karyawan tidak ditemukan.');
        }

        $this->render('edit', [
            'employee' => $employee,
            'departments' => $this->departments(),
            'positions' => $this->positions(),
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan.');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $existing = $id ? $this->model()->find($id) : null;

        if (!$existing) {
            http_response_code(404);
            exit('Data karyawan tidak ditemukan.');
        }

        $input = $this->formData($_POST);
        $errors = $this->validate($_POST, $id);

        if ($errors) {
            $this->render('edit', [
                'employee' => array_merge($existing, $input),
                'departments' => $this->departments(),
                'positions' => $this->positions(),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model()->update($id, $input);
            $this->flash('Data karyawan berhasil diperbarui.');
            $this->redirectTo('employees');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Data gagal diperbarui. Periksa kembali input.');
            redirect('index.php?page=employee-edit&id=' . $id);
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan.');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Token keamanan tidak valid.');
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || !$this->model()->find($id)) {
            $this->flash('Data karyawan tidak ditemukan.');
            $this->redirectTo('employees');
        }

        try {
            $this->model()->delete($id);
            $this->flash('Data karyawan berhasil dihapus.');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->flash('Data tidak dapat dihapus.');
        }

        $this->redirectTo('employees');
    }
}
