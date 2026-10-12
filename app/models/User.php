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

    public function all(string $search, int $limit, int $offset): array
    {
        $sql = 'SELECT id, name, email, role, created_at
                FROM users';

        $params = [];

        if ($search !== '') {
            $sql .= ' WHERE name LIKE :name OR email LIKE :email';
            $params['name'] = '%' . $search . '%';
            $params['email'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM users';
        $params = [];

        if ($search !== '') {
            $sql .= ' WHERE name LIKE :name OR email LIKE :email';
            $params = [
                'name' => '%' . $search . '%',
                'email' => '%' . $search . '%',
            ];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, role, created_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function countAdmins(): int
    {
        return (int) $this->pdo
            ->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")
            ->fetchColumn();
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, role)
             VALUES (:name, :email, :password, :role)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            ),
            'role' => $data['role'],
        ]);
    }

    public function update(int $id, array $data): void
    {
        if ($data['password'] !== '') {
            $stmt = $this->pdo->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     role = :role,
                     password = :password
                 WHERE id = :id'
            );

            $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'password' => password_hash(
                    $data['password'],
                    PASSWORD_DEFAULT
                ),
                'id' => $id,
            ]);

            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET name = :name,
                 email = :email,
                 role = :role
             WHERE id = :id'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM users WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
    }
}
