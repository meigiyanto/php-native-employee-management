<?php

declare(strict_types=1);

final class Department
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(string $search = ''): array
    {
        $sql = '
            SELECT d.id, d.name, d.description,
                   d.created_at, d.updated_at,
                   (SELECT COUNT(*) FROM employees e
                    WHERE e.department_id = d.id) AS employee_count,
                   (SELECT COUNT(*) FROM positions p
                    WHERE p.department_id = d.id) AS position_count
            FROM departments d
        ';

        $params = [];

        if ($search !== '') {
            $sql .= ' WHERE d.name LIKE :name
                      OR d.description LIKE :description';
            $term = '%' . $search . '%';
            $params = [
                'name' => $term,
                'description' => $term,
            ];
        }

        $sql .= ' ORDER BY d.name ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT d.id, d.name, d.description,
                    d.created_at, d.updated_at,
                    (SELECT COUNT(*) FROM employees e
                     WHERE e.department_id = d.id) AS employee_count,
                    (SELECT COUNT(*) FROM positions p
                     WHERE p.department_id = d.id) AS position_count
             FROM departments d
             WHERE d.id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $department = $stmt->fetch();

        return $department ?: null;
    }

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM departments WHERE name = :name';
        $params = ['name' => $name];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO departments (name, description)
             VALUES (:name, :description)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE departments
             SET name = :name, description = :description
             WHERE id = :id'
        );

        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM departments WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
