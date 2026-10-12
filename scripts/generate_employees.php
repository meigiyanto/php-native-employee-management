<?php

declare(strict_types=1);

/*
 * Generate data karyawan dummy menggunakan Faker.
 *
 * Penggunaan:
 * php scripts/generate_employees.php
 * php scripts/generate_employees.php 50
 * php scripts/generate_employees.php 100
 */

if (PHP_SAPI !== 'cli') {
    exit("Skrip hanya boleh dijalankan melalui CLI.\n");
}

require_once dirname(__DIR__) . '/app/config/database.php';

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Faker\Factory;

$faker = Factory::create('id_ID');

$count = isset($argv[1]) ? filter_var($argv[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5000]]) : 50;

if ($count === false) {
    fwrite(STDERR, "Jumlah harus berupa angka 1-5000.\n");
    exit(1);
}

try {
    // Ambil pasangan departemen dan jabatan yang valid.
    $stmt = $pdo->query(
        'SELECT p.id AS position_id, p.department_id
        FROM positions p
        INNER JOIN departments d
        ON d.id = p.department_id
        ORDER BY p.id'
    );

    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($positions === []) {
        throw new RuntimeException(
            'Belum ada jabatan yang terhubung ke departemen.'
        );
    }

    $insert = $pdo->prepare(
        'INSERT INTO employees (employee_code, full_name, email, phone, gender, birth_date, hire_date, department_id, position_id, employment_status, salary, address, created_at, updated_at)
        VALUES (:employee_code, :full_name, :email, :phone, :gender, :birth_date, :hire_date, :department_id, :position_id, :employment_status, :salary, :address, NOW(), NOW())'
    );

    $updateCode = $pdo->prepare(
        'UPDATE employees
        SET employee_code = :employee_code
        WHERE id = :id'
    );

    $statuses = [ 'active', 'active', 'active', 'active', 'inactive', 'on_leave', 'resigned' ];

    $pdo->beginTransaction();

    for ($i = 0; $i < $count; $i++) {
        $position = $faker->randomElement($positions);

        $birthDate = $faker->dateTimeBetween('-60 years','-18 years')->format('Y-m-d');

        $minimumHireDate = (new DateTimeImmutable($birthDate))->modify('+18 years')->format('Y-m-d');

        $hireDate = $faker->dateTimeBetween($minimumHireDate, 'now')->format('Y-m-d');

        $gender = $faker->randomElement([
            'male',
            'female',
            'other',
        ]);

        // Domain khusus data contoh, bukan email karyawan sungguhan.
        $email = 'employee.' . bin2hex(random_bytes(8)) . '@example.test';

        $insert->execute([
            'employee_code' => 'TMP-' . bin2hex(random_bytes(8)),
            'full_name' => $faker->name(),
            'email' => $email,
            'phone' => $faker->phoneNumber(),
            'gender' => $gender,
            'birth_date' => $birthDate,
            'hire_date' => $hireDate,
            'department_id' => $position['department_id'],
            'position_id' => $position['position_id'],
            'employment_status' => $faker->randomElement($statuses),
            'salary' => $faker->numberBetween(3500000, 20000000),
            'address' => $faker->address(),
        ]);

        $id = (int) $pdo->lastInsertId();

        // Samakan pola kode dengan Employee::create().
        $employeeCode = 'EMP-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);

        $updateCode->execute(['employee_code' => $employeeCode, 'id' => $id]);
    }

    $pdo->commit();

    $total = (int) $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();

    echo "Berhasil membuat {$count} data karyawan dummy.\n";
    echo "Total karyawan dalam database: {$total}\n";

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Gagal: {$e->getMessage()}\n");
    exit(1);
}
