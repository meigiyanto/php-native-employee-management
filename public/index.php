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
require_once __DIR__ . '/../app/models/Position.php';
require_once __DIR__ . '/../app/controllers/PositionController.php';

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

        'employees' => ['GET', 'index'],
        'employee-export' => ['GET', 'exportCsv'],
        'employee-create' => ['GET', 'create'],

        'employees' => ['GET', 'index'],
        'employee-import-template' => ['GET', 'downloadImportTemplate'],
        'employee-import' => ['POST', 'importCsv'],

        'employee-delete' => ['POST', 'delete'],
        'employee-bulk-delete' => ['POST', 'bulkDelete'],

        'departments' => ['GET', 'index'],
        'department-create' => ['GET', 'create'],
        'department-show' => ['GET', 'show'],
        'department-edit' => ['GET', 'edit'],
        'department-store' => ['POST', 'store'],
        'department-update' => ['POST', 'update'],
        'department-delete' => ['POST', 'delete'],

        'positions' => ['GET', 'index'],
        'position-create' => ['GET', 'create'],
        'position-show' => ['GET', 'show'],
        'position-edit' => ['GET', 'edit'],
        'position-store' => ['POST', 'store'],
        'position-update' => ['POST', 'update'],
        'position-delete' => ['POST', 'delete'],
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

    $departmentPages = [
        'departments',
        'department-create',
        'department-show',
        'department-edit',
        'department-store',
        'department-update',
        'department-delete',
    ];

    $positionPages = [
        'positions',
        'position-create',
        'position-show',
        'position-edit',
        'position-store',
        'position-update',
        'position-delete',
    ];

    if (in_array($page, $departmentPages, true)) {
        $departmentController = new DepartmentController($pdo);
        $departmentController->$action();
    } elseif (in_array($page, $positionPages, true)) {
        $positionController = new PositionController($pdo);
        $positionController->$action();
    } else {
        $employeeController->$action();
    }

    exit;
}

$controller = new DashboardController($pdo);
$data = $controller->index();


// Data yang dikirim oleh DashboardController
$totalEmployees = $data['totalEmployees'] ?? 0;
$activeEmployees = $data['activeEmployees'] ?? 0;
$totalDepartments = $data['totalDepartments'] ?? 0;
$totalPositions = $data['totalPositions'] ?? 0;
$recentEmployees = $data['recentEmployees'] ?? [];

// Data untuk kartu statistik dashboard
$cards = [
    [
        'title' => 'Total Karyawan',
        'value' => $totalEmployees,
        'icon' => 'bi-people-fill',
        'color' => 'primary',
    ],
    [
        'title' => 'Karyawan Aktif',
        'value' => $activeEmployees,
        'icon' => 'bi-person-check-fill',
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
        'icon' => 'bi-briefcase-fill',
        'color' => 'info',
    ],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard Sistem Manajemen Data Karyawan">

    <title>Dashboard | <?= e(APP_NAME) ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="dashboard-page">
<?php
    $currentUser = $_SESSION['auth_user'] ?? [];
    $userName = trim((string) ($currentUser['name'] ?? 'Pengguna'));
    $userRole = (string) ($currentUser['role'] ?? '');
?>

    <nav class="navbar navbar-expand-lg dashboard-navbar">
        <div class="container-fluid px-3 px-lg-4">

            <a class="navbar-brand fw-bold" href="index.php">
                <span class="brand-icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                Employee Management
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#dashboardNavbar"
                aria-controls="dashboardNavbar"
                aria-expanded="false"
                aria-label="Buka navigasi"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="dashboardNavbar">
                <ul class="navbar-nav me-auto ms-lg-4 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-grid-1x2 me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=employees">
                            <i class="bi bi-people me-1"></i>
                            Karyawan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=departments">
                            <i class="bi bi-building me-1"></i>
                            Departemen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=positions">
                            <i class="bi bi-briefcase me-1"></i>
                            Jabatan
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?= e(strtoupper(substr($userName, 0, 1))) ?>
                        </div>
                        <div class="user-profile-text">
                            <span class="user-name"><?= e($userName) ?></span>
                            <span class="user-role">
                                <?= e(strtoupper($userRole)) ?>
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="index.php?page=logout">
                        <input
                            type="hidden"
                            name="_token"
                            value="<?= e(csrfToken()) ?>"
                        >
                        <button
                            type="submit"
                            class="btn btn-outline-danger btn-sm"
                            aria-label="Logout"
                        >
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">

        <!-- Header dashboard -->
        <section class="dashboard-welcome mb-4 mb-lg-5">
            <div>
                <span class="welcome-eyebrow">OVERVIEW</span>
                <h1 class="welcome-title">
                    Selamat datang, <?= e($userName) ?>!
                </h1>
                <p class="text-secondary mb-0">
                    Pantau data karyawan dan struktur organisasi Anda
                    melalui ringkasan berikut.
                </p>
            </div>

            <div class="welcome-date">
                <i class="bi bi-calendar3"></i>
                <span><?= e(date('d-m-Y')) ?></span>
            </div>
        </section>

        <!-- Statistik -->
        <section class="mb-4 mb-lg-5">
            <div class="section-heading">
                <div>
                    <h2 class="section-title">Ringkasan Data</h2>
                    <p class="section-description">
                        Kondisi data organisasi saat ini.
                    </p>
                </div>
            </div>

            <div class="row g-3 g-xl-4">
                <?php foreach ($cards as $card): ?>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <article class="stat-card h-100">
                            <div class="stat-card-top">
                                <span class="stat-label">
                                    <?= e($card['title']) ?>
                                </span>

                                <span class="stat-icon stat-icon-<?= e($card['color']) ?>">
                                    <i class="bi <?= e($card['icon']) ?>"></i>
                                </span>
                            </div>

                            <div class="stat-value">
                                <?= e(number_format((int) $card['value'], 0, ',', '.')) ?>
                            </div>

                            <div class="stat-card-bottom">
                                <span class="stat-dot stat-dot-<?= e($card['color']) ?>"></span>
                                Data terdaftar dalam sistem
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Akses cepat -->
        <section class="mb-4 mb-lg-5">
            <div class="section-heading">
                <div>
                    <h2 class="section-title">Akses Cepat</h2>
                    <p class="section-description">
                        Buka bagian yang ingin Anda kelola.
                    </p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <a href="index.php?page=employees" class="quick-link">
                        <span class="quick-link-icon quick-icon-blue">
                            <i class="bi bi-people"></i>
                        </span>
                        <span class="quick-link-content">
                            <strong>Data Karyawan</strong>
                            <small>Lihat dan kelola data karyawan</small>
                        </span>
                        <i class="bi bi-arrow-up-right quick-link-arrow"></i>
                    </a>
                </div>

                <div class="col-12 col-md-4">
                    <a href="index.php?page=departments" class="quick-link">
                        <span class="quick-link-icon quick-icon-purple">
                            <i class="bi bi-building"></i>
                        </span>
                        <span class="quick-link-content">
                            <strong>Departemen</strong>
                            <small>Kelola unit organisasi</small>
                        </span>
                        <i class="bi bi-arrow-up-right quick-link-arrow"></i>
                    </a>
                </div>

                <div class="col-12 col-md-4">
                    <a href="index.php?page=positions" class="quick-link">
                        <span class="quick-link-icon quick-icon-orange">
                            <i class="bi bi-briefcase"></i>
                        </span>
                        <span class="quick-link-content">
                            <strong>Jabatan</strong>
                            <small>Kelola jabatan karyawan</small>
                        </span>
                        <i class="bi bi-arrow-up-right quick-link-arrow"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Karyawan terbaru -->
        <section class="recent-card">
            <div class="recent-card-header">
                <div>
                    <h2 class="section-title">Karyawan Terbaru</h2>
                    <p class="section-description mb-0">
                        Lima data karyawan yang terakhir ditambahkan.
                    </p>
                </div>

                <a
                    href="index.php?page=employees"
                    class="btn btn-primary btn-sm"
                >
                    Lihat semua
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table dashboard-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 ps-lg-4">Karyawan</th>
                            <th>Departemen</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($recentEmployees === []): ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <strong>Belum ada data karyawan</strong>
                                <span>
                                    Data karyawan akan ditampilkan di sini
                                    setelah ditambahkan.
                                </span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentEmployees as $employee): ?>
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

                            <tr>
                                <td class="ps-3 ps-lg-4">
                                    <div class="employee-cell">
                                        <div class="employee-avatar">
                                            <?= e(strtoupper(substr(
                                                (string) $employee['full_name'],
                                                0,
                                                1
                                            ))) ?>
                                        </div>

                                        <div class="employee-details">
                                            <strong>
                                                <?= e($employee['full_name']) ?>
                                            </strong>
                                            <span>
                                                <?= e($employee['employee_code']) ?>
                                            </span>
                                            <small>
                                                <?= e($employee['email']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td><?= e($employee['department_name']) ?></td>
                                <td><?= e($employee['position_name']) ?></td>

                                <td>
                                    <span class="status-pill status-<?= e($statusClass) ?>">
                                        <span class="status-indicator"></span>
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

            <div class="recent-card-footer">
                <span>
                    <i class="bi bi-info-circle me-1"></i>
                    Data diperbarui berdasarkan catatan terbaru.
                </span>
                <a href="index.php?page=employees">
                    Buka data karyawan
                    <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
        </section>

        <footer class="dashboard-footer">
            Employee Management System
            <span>&middot;</span>
            PHP Native
        </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
