<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/database.php';

$name = 'Administrator';
$email = 'admin@example.com';
$password = 'GantiPasswordKuat123!';

$check = $pdo->prepare(
    'SELECT id FROM users WHERE email = :email LIMIT 1'
);

$check->execute(['email' => $email]);

if ($check->fetch()) {
    exit("Email admin sudah terdaftar.\n");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password, role)
     VALUES (:name, :email, :password, :role)'
);

$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password' => $hash,
    'role' => 'admin',
]);

echo "Akun admin berhasil dibuat.\n";
echo "Email: {$email}\n";
echo "Ganti kata sandi contoh sebelum menjalankan skrip ini.\n";
