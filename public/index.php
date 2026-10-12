<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/config/database.php';

require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Employee.php';
require_once __DIR__ . '/../app/controllers/EmployeeController.php';
require_once __DIR__ . '/../app/models/Department.php';
require_once __DIR__ . '/../app/controllers/DepartmentController.php';

if (isset($_GET['page'])) {
    $employeeController = new EmployeeController($pdo);

    $page = (string) ($_GET['page'] ?? '');
    $method = $_SERVER['REQUEST_METHOD'];

    $authController = new AuthController($pdo);

    // Tampilkan halaman login.
    if ($page === 'login' && $method === 'GET') {
        $authController->login();
        exit;
    }

    // Proses form login.
    if ($page === 'login-submit' && $method === 'POST') {
        $authController->authenticate();
        exit;
    }

    // Proses logout.
    if ($page === 'logout') {
        $authController->logout();
        exit;
    }

    // Lindungi halaman aplikasi dari pengguna yang belum login.
    if (empty($_SESSION['auth_user'])) {
        redirect('index.php?page=login');
    }

    // Pastikan role pengguna valid.
    if (!in_array($_SESSION['auth_user']['role'] ?? '', ['admin', 'hr'], true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }

    $routes = [
        'employees' => ['GET', 'index'],
        'employee-create' => ['GET', 'create'],
        'employee-show' => ['GET', 'show'],
        'employee-edit' => ['GET', 'edit'],
        'employee-store' => ['POST', 'store'],
        'employee-update' => ['POST', 'update'],
        'employee-delete' => ['POST', 'delete'],

        'departments' => ['GET', 'index'],
        'department-create' => ['GET', 'create'],
        'department-show' => ['GET', 'show'],
        'department-edit' => ['GET', 'edit'],
        'department-store' => ['POST', 'store'],
        'department-update' => ['POST', 'update'],
        'department-delete' => ['POST', 'delete'],
    ];

    if (!isset($routes[$page])) {
        http_response_code(404);
        exit('Halaman tidak ditemukan.');
    }

    [$expectedMethod, $action] = $routes[$page];

    if ($method !== $expectedMethod) {
        header('Allow: ' . $expectedMethod);
        http_response_code(405);
        exit('Metode permintaan tidak diizinkan.');
    }

    // $employeeController->$action();
    $departmentPages = [
        'departments',
        'department-create',
        'department-show',
        'department-edit',
        'department-store',
        'department-update',
        'department-delete',
    ];

    if (in_array($page, $departmentPages, true)) {
        $departmentController = new DepartmentController($pdo);
        $departmentController->$action();
    } else {
        $employeeController->$action();
    }

    exit;
}

$controller = new DashboardController($pdo);
$data = $controller->index();

$totalEmployees = $data['totalEmployees'];
$activeEmployees = $data['activeEmployees'];
$totalDepartments = $data['totalDepartments'];
$totalPositions = $data['totalPositions'];
$recentEmployees = $data['recentEmployees'];

$cards = [
    [
        'title' => 'Total Karyawan',
        'value' => $totalEmployees,
        'icon' => 'bi-people',
        'color' => 'primary',
    ],
    [
        'title' => 'Karyawan Aktif',
        'value' => $activeEmployees,
        'icon' => 'bi-person-check',
        'color' => 'success',
    ],
    [
        'title' => 'Departemen',
        'value' => $totalDepartments,
        'icon' => 'bi-building',
        'color' => 'warning',
    ],
    [
        'title' => 'Jabatan',
        'value' => $totalPositions,
        'icon' => 'bi-briefcase',
        'color' => 'info',
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Dashboard Sistem Manajemen Data Karyawan">
    <title>Dashboard | <?= e(APP_NAME) ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-people-fill me-2"></i>
                Employee Management
            </a>

            <span class="navbar-text text-white small">
                Panel Administrasi
            </span>

            <form method="POST" action="index.php?page=logout">
                <input
                    type="hidden"
                    name="_token"
                    value="<?= e(csrfToken()) ?>"
                >

                <button type="submit" class="btn btn-outline-danger">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container-fluid px-3 px-lg-4 py-4">

        <div class="d-flex flex-wrap justify-content-between  align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Dashboard</h1>
                <p class="text-secondary mb-0">
                    Ringkasan informasi karyawan dan organisasi.
                </p>
            </div>

            <span class="badge text-bg-light border p-2">
                <i class="bi bi-calendar3 me-1"></i>
                <?= e(date('d-m-Y')) ?>
            </span>
        </div>

        <div class="row g-3 mb-4">
            <?php foreach ($cards as $card): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card dashboard-card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-secondary small mb-2">
                                        <?= e($card['title']) ?>
                                    </p>
                                    <h2 class="h3 fw-bold mb-0">
                                        <?= e($card['value']) ?>
                                    </h2>
                                </div>

                                <div class="stat-icon text-bg-<?= e($card['color']) ?>">
                                    <i class="bi <?= e($card['icon']) ?>"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 p-3 p-md-4">
                <div class="d-flex justify-content-between
                            align-items-center gap-2">
                    <div>
                        <h2 class="h5 fw-bold mb-1">
                            Karyawan Terbaru
                        </h2>
                        <p class="small text-secondary mb-0">
                            Lima data karyawan terakhir.
                        </p>
                    </div>
                    <!--
                    <button type="button" class="btn btn-primary btn-sm"
                            disabled
                            title="Akan diaktifkan pada tahap CRUD">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Karyawan
                    </button>
                    -->
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 ps-md-4">Kode</th>
                            <th>Nama Karyawan</th>
                            <th>Departemen</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($recentEmployees === []): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-inbox display-5
                                          text-secondary"></i>
                                <p class="fw-semibold mt-3 mb-1">
                                    Belum ada data karyawan
                                </p>
                                <p class="text-secondary small mb-0">
                                    Data karyawan akan tampil di sini
                                    setelah ditambahkan.
                                </p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentEmployees as $employee): ?>
                            <tr>
                                <td class="ps-3 ps-md-4">
                                    <?= e($employee['employee_code']) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <?= e($employee['full_name']) ?>
                                    </div>
                                    <div class="small text-secondary">
                                        <?= e($employee['email']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?= e($employee['department_name']) ?>
                                </td>
                                <td>
                                    <?= e($employee['position_name']) ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match (
                                        $employee['employment_status']
                                    ) {
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'on_leave' => 'warning',
                                        'resigned' => 'danger',
                                        default => 'secondary',
                                    };
                                    ?>
                                    <span class="badge text-bg-<?= e($statusClass) ?>">
                                        <?= e(statusLabel(
                                            $employee['employment_status']
                                        )) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-0 p-3">
                <span class="text-secondary small">
                    Sistem Manajemen Data Karyawan
                </span>
            </div>
        </div>

    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
