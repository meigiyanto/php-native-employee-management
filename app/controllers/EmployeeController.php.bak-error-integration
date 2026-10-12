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

    public function exportCsv(): void
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

        $employees = $this->model()->exportAll($search, $filters);

        $filename = 'data-karyawan-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header(
            'Content-Disposition: attachment; filename="' . $filename . '"'
        );
        header('Cache-Control: no-store, no-cache, must-revalidate');

        // BOM membantu Excel mengenali karakter UTF-8.
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        if ($output === false) {
            exit;
        }

        fputcsv($output, [
            'Kode Karyawan',
            'Nama Lengkap',
            'Email',
            'Telepon',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Tanggal Masuk',
            'Departemen',
            'Jabatan',
            'Status',
            'Gaji',
            'Alamat',
        ], ',', '"', '');

        foreach ($employees as $employee) {
            $row = [
                $employee['employee_code'],
                $employee['full_name'],
                $employee['email'],
                $employee['phone'],
                $employee['gender'],
                $employee['birth_date'],
                $employee['hire_date'],
                $employee['department_name'],
                $employee['position_name'],
                statusLabel((string) $employee['employment_status']),
                $employee['salary'],
                $employee['address'],
            ];

            // Mencegah isi teks diperlakukan sebagai formula spreadsheet.
            foreach ($row as &$value) {
                if (
                    is_string($value)
                    && preg_match('/^[\t\r\n ]*[=+\-@]/', $value)
                ) {
                    $value = "'" . $value;
                }
            }
            unset($value);

            fputcsv($output, $row, ',', '"', '');
        }

        fclose($output);
        exit;
    }

    public function downloadImportTemplate(): void
    {
        $filename = 'template-import-karyawan.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header(
            'Content-Disposition: attachment; filename="' . $filename . '"'
        );
        header('Cache-Control: no-store');

        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        if ($output === false) {
            exit;
        }

        fputcsv($output, [
            'employee_code',
            'full_name',
            'email',
            'phone',
            'gender',
            'birth_date',
            'hire_date',
            'department',
            'position',
            'employment_status',
            'salary',
            'address',
        ], ',', '"', '');

        fputcsv($output, [
            'EMP-0005',
            'Budi Santoso',
            'budi@example.com',
            '08123456789',
            'male',
            '1995-04-12',
            '2026-01-15',
            'IT',
            'Web Developer',
            'active',
            '5000000.00',
            'Bandung',
        ], ',', '"', '');

        fclose($output);
        exit;
    }

    public function importCsv(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan.');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Token keamanan tidak valid. Muat ulang halaman.');
        }

        if (
            !isset($_FILES['csv_file'])
            || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK
        ) {
            $this->flash('File CSV gagal diunggah. Silakan coba lagi.');
            $this->redirectTo('employees');
        }

        $file = $_FILES['csv_file'];

        // Maksimal 5 MB.
        if (
            !is_uploaded_file($file['tmp_name'])
            || $file['size'] <= 0
            || $file['size'] > 5 * 1024 * 1024
            || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv'
        ) {
            $this->flash('Pilih file CSV yang valid dengan ukuran maksimal 5 MB.');
            $this->redirectTo('employees');
        }

        $handle = fopen($file['tmp_name'], 'r');

        if ($handle === false) {
            $this->flash('File CSV tidak dapat dibaca.');
            $this->redirectTo('employees');
        }

        $expectedHeaders = [
            'employee_code',
            'full_name',
            'email',
            'phone',
            'gender',
            'birth_date',
            'hire_date',
            'department',
            'position',
            'employment_status',
            'salary',
            'address',
        ];

        $headers = fgetcsv($handle, 0, ',', '"', '');

        if (!$headers) {
            fclose($handle);
            $this->flash('File CSV kosong atau tidak memiliki header.');
            $this->redirectTo('employees');
        }

        // Menghapus BOM UTF-8 jika file dibuat oleh Excel.
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        if ($headers !== $expectedHeaders) {
            fclose($handle);
            $this->flash(
                'Header CSV tidak sesuai. Unduh dan gunakan template resmi.'
            );
            $this->redirectTo('employees');
        }

        $rows = [];
        $errors = [];
        $seenCodes = [];
        $seenEmails = [];
        $line = 1;

        $allowedGenders = ['', 'male', 'female', 'other'];
        $allowedStatuses = [
            'active',
            'inactive',
            'on_leave',
            'resigned',
        ];

        $validDate = static function (string $value): bool {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

            return $date !== false
                && $date->format('Y-m-d') === $value;
        };

        // Ambil referensi departemen dan jabatan yang sudah ada.
        $departmentRows = $this->pdo->query(
            'SELECT id, name FROM departments'
        )->fetchAll(PDO::FETCH_ASSOC);

        $departmentMap = [];

        foreach ($departmentRows as $department) {
            $key = mb_strtolower(trim($department['name']));
            $departmentMap[$key] = (int) $department['id'];
        }

        $positionRows = $this->pdo->query(
            'SELECT p.id, p.name, p.department_id, d.name AS department_name
             FROM positions p
             JOIN departments d ON d.id = p.department_id'
        )->fetchAll(PDO::FETCH_ASSOC);

        $positionMap = [];

        foreach ($positionRows as $position) {
            $key = mb_strtolower(
                trim($position['department_name']) . '|'
                . trim($position['name'])
            );

            $positionMap[$key] = [
                'id' => (int) $position['id'],
                'department_id' => (int) $position['department_id'],
            ];
        }

        $checkCode = $this->pdo->prepare(
            'SELECT COUNT(*) FROM employees WHERE employee_code = :value'
        );

        $checkEmail = $this->pdo->prepare(
            'SELECT COUNT(*) FROM employees WHERE email = :value'
        );

        while (($data = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $line++;

            // Lewati baris kosong.
            if ($data === [null] || $data === []) {
                continue;
            }

            if (count($data) !== count($expectedHeaders)) {
                $errors[] = "Baris {$line}: jumlah kolom tidak sesuai.";
                continue;
            }

            $row = array_combine($expectedHeaders, $data);

            foreach ($row as $key => $value) {
                $row[$key] = trim((string) ($value ?? ''));
            }

            $code = $row['employee_code'];
            $name = $row['full_name'];
            $email = $row['email'];
            $gender = $row['gender'];
            $birthDate = $row['birth_date'];
            $hireDate = $row['hire_date'];
            $departmentName = $row['department'];
            $positionName = $row['position'];
            $status = $row['employment_status'] ?: 'active';
            $salary = $row['salary'];

            if (
                $code === ''
                || mb_strlen($code) > 20
            ) {
                $errors[] = "Baris {$line}: kode karyawan wajib diisi, maksimal 20 karakter.";
            }

            if ($name === '' || mb_strlen($name) > 150) {
                $errors[] = "Baris {$line}: nama wajib diisi, maksimal 150 karakter.";
            }

            if (
                !filter_var($email, FILTER_VALIDATE_EMAIL)
                || strlen($email) > 150
            ) {
                $errors[] = "Baris {$line}: alamat email tidak valid.";
            }

            if (strlen($row['phone']) > 25) {
                $errors[] = "Baris {$line}: telepon maksimal 25 karakter.";
            }

            if (!in_array($gender, $allowedGenders, true)) {
                $errors[] = "Baris {$line}: nilai gender tidak valid.";
            }

            if ($birthDate !== '' && (
                !$validDate($birthDate)
                || $birthDate > date('Y-m-d')
            )) {
                $errors[] = "Baris {$line}: tanggal lahir tidak valid.";
            }

            if ($hireDate === '' || !$validDate($hireDate)) {
                $errors[] = "Baris {$line}: tanggal masuk wajib berformat YYYY-MM-DD.";
            }

            $departmentKey = mb_strtolower($departmentName);

            if (
                $departmentName === ''
                || !isset($departmentMap[$departmentKey])
            ) {
                $errors[] = "Baris {$line}: departemen '{$departmentName}' tidak ditemukan.";
                $departmentId = null;
            } else {
                $departmentId = $departmentMap[$departmentKey];
            }

            $positionKey = mb_strtolower(
                $departmentName . '|' . $positionName
            );

            if (
                $departmentId === null
                || !isset($positionMap[$positionKey])
                || $positionMap[$positionKey]['department_id'] !== $departmentId
            ) {
                $errors[] = "Baris {$line}: jabatan tidak ditemukan pada departemen tersebut.";
                $positionId = null;
            } else {
                $positionId = $positionMap[$positionKey]['id'];
            }

            if (!in_array($status, $allowedStatuses, true)) {
                $errors[] = "Baris {$line}: status karyawan tidak valid.";
            }

            if (
                !preg_match('/^\d{1,13}(\.\d{1,2})?$/', $salary)
                || (float) $salary < 0
            ) {
                $errors[] = "Baris {$line}: format gaji tidak valid.";
            }

            if (mb_strlen($row['address']) > 65535) {
                $errors[] = "Baris {$line}: alamat terlalu panjang.";
            }

            $codeKey = mb_strtolower($code);
            $emailKey = mb_strtolower($email);

            if ($code !== '') {
                if (isset($seenCodes[$codeKey])) {
                    $errors[] = "Baris {$line}: kode karyawan duplikat dalam file.";
                } else {
                    $seenCodes[$codeKey] = true;

                    $checkCode->execute(['value' => $code]);

                    if ((int) $checkCode->fetchColumn() > 0) {
                        $errors[] = "Baris {$line}: kode karyawan sudah ada di database.";
                    }
                }
            }

            if ($email !== '') {
                if (isset($seenEmails[$emailKey])) {
                    $errors[] = "Baris {$line}: email duplikat dalam file.";
                } else {
                    $seenEmails[$emailKey] = true;

                    $checkEmail->execute(['value' => $email]);

                    if ((int) $checkEmail->fetchColumn() > 0) {
                        $errors[] = "Baris {$line}: email sudah ada di database.";
                    }
                }
            }

            $row['_department_id'] = $departmentId;
            $row['_position_id'] = $positionId;
            $row['_status'] = $status;

            $rows[] = $row;

            // Batasi jumlah data agar import tetap ringan.
            if (count($rows) > 500) {
                $errors[] = 'Maksimal 500 baris data per proses import.';
                break;
            }

            if (count($errors) >= 20) {
                $errors[] = 'Validasi dihentikan setelah ditemukan banyak kesalahan.';
                break;
            }
        }

        fclose($handle);

        if (!$rows) {
            $errors[] = 'Tidak ditemukan baris data untuk diimpor.';
        }

        if ($errors) {
            $message = 'Import dibatalkan: '
                . implode(' ', array_slice($errors, 0, 8));

            $this->flash($message);
            $this->redirectTo('employees');
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO employees (
                employee_code,
                full_name,
                email,
                phone,
                gender,
                birth_date,
                hire_date,
                department_id,
                position_id,
                employment_status,
                salary,
                address,
                created_at,
                updated_at
            ) VALUES (
                :employee_code,
                :full_name,
                :email,
                :phone,
                :gender,
                :birth_date,
                :hire_date,
                :department_id,
                :position_id,
                :employment_status,
                :salary,
                :address,
                NOW(),
                NOW()
            )'
        );

        try {
            $this->pdo->beginTransaction();

            foreach ($rows as $row) {
                $insert->execute([
                    'employee_code' => $row['employee_code'],
                    'full_name' => $row['full_name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'] !== '' ? $row['phone'] : null,
                    'gender' => $row['gender'] !== '' ? $row['gender'] : null,
                    'birth_date' => $row['birth_date'] !== '' ? $row['birth_date'] : null,
                    'hire_date' => $row['hire_date'],
                    'department_id' => $row['_department_id'],
                    'position_id' => $row['_position_id'],
                    'employment_status' => $row['_status'],
                    'salary' => $row['salary'],
                    'address' => $row['address'] !== '' ? $row['address'] : null,
                ]);
            }

            $this->pdo->commit();

            $this->flash(
                count($rows) . ' data karyawan berhasil diimpor.'
            );
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log($exception->getMessage());

            $this->flash(
                'Import gagal. Tidak ada data dari proses ini yang disimpan.'
            );
        }

        $this->redirectTo('employees');
    }

    public function bulkDelete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan.');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Token keamanan tidak valid. Muat ulang halaman.');
        }

        $submittedIds = $_POST['employee_ids'] ?? [];

        if (!is_array($submittedIds) || $submittedIds === []) {
            $this->flash('Pilih minimal satu karyawan untuk dihapus.');
            $this->redirectTo('employees');
        }

        // Validasi ID dan hilangkan ID duplikat.
        $ids = [];

        foreach ($submittedIds as $submittedId) {
            $id = filter_var($submittedId, FILTER_VALIDATE_INT);

            if ($id === false || $id < 1) {
                $this->flash('Pilihan ID karyawan tidak valid.');
                $this->redirectTo('employees');
            }

            $ids[] = $id;
        }

        $ids = array_values(array_unique($ids));

        // Batasi jumlah penghapusan dalam satu permintaan.
        if (count($ids) > 100) {
            $this->flash('Maksimal 100 karyawan dapat dihapus sekaligus.');
            $this->redirectTo('employees');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            $this->pdo->beginTransaction();

            // Pastikan seluruh ID yang dipilih masih ada.
            $check = $this->pdo->prepare(
                "SELECT COUNT(*) FROM employees WHERE id IN ($placeholders)"
            );
            $check->execute($ids);

            if ((int) $check->fetchColumn() !== count($ids)) {
                $this->pdo->rollBack();
                $this->flash(
                    'Sebagian data tidak ditemukan. Muat ulang tabel dan coba lagi.'
                );
                $this->redirectTo('employees');
            }

            $stmt = $this->pdo->prepare(
                "DELETE FROM employees WHERE id IN ($placeholders)"
            );
            $stmt->execute($ids);

            $deletedCount = $stmt->rowCount();

            $this->pdo->commit();

            $this->flash(
                $deletedCount . ' data karyawan berhasil dihapus.'
            );
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log($exception->getMessage());

            $this->flash(
                'Penghapusan gagal. Periksa relasi data di database.'
            );
        }

        $this->redirectTo('employees');
    }

}
