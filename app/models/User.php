<?php

declare(strict_types=1);

final class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, password, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}
