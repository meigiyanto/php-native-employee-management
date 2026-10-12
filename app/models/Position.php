<?php

declare(strict_types=1);

final class Position
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function all(string $search = ''): array
    {
        $sql = "
            SELECT
                p.id,
                p.department_id,
                p.name,
                p.description,
                p.created_at,
                p.updated_at,
                d.name AS department_name,
                (
                    SELECT COUNT(*)
                    FROM employees e
                    WHERE e.position_id = p.id
                      AND e.department_id = p.department_id
                ) AS employee_count
            FROM positions p
            INNER JOIN departments d
                ON d.id = p.department_id
        ";

        $params = [];

        if ($search !== '') {
            $sql .= "
                WHERE p.name LIKE :search_name
                   OR p.description LIKE :search_description
                   OR d.name LIKE :search_department
            ";

            $keyword = '%' . $search . '%';

            $params = [
                'search_name' => $keyword,
                'search_description' => $keyword,
                'search_department' => $keyword,
            ];
        }

        $sql .= ' ORDER BY d.name ASC, p.name ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $sql = "
            SELECT
                p.id,
                p.department_id,
                p.name,
                p.description,
                p.created_at,
                p.updated_at,
                d.name AS department_name,
                (
                    SELECT COUNT(*)
                    FROM employees e
                    WHERE e.position_id = p.id
                      AND e.department_id = p.department_id
                ) AS employee_count
            FROM positions p
            INNER JOIN departments d
                ON d.id = p.department_id
            WHERE p.id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $position = $stmt->fetch(PDO::FETCH_ASSOC);

        return $position ?: null;
    }

    public function departmentExists(int $departmentId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM departments WHERE id = :id LIMIT 1'
        );

        $stmt->execute(['id' => $departmentId]);

        return (bool) $stmt->fetchColumn();
    }

    public function nameExists(
        int $departmentId,
        string $name,
        ?int $excludeId = null
    ): bool {
        $sql = "
            SELECT 1
            FROM positions
            WHERE department_id = :department_id
              AND name = :name
        ";

        $params = [
            'department_id' => $departmentId,
            'name' => $name,
        ];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $sql = "
            INSERT INTO positions (
                department_id,
                name,
                description
            ) VALUES (
                :department_id,
                :name,
                :description
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'department_id' => $data['department_id'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE positions
            SET
                department_id = :department_id,
                name = :name,
                description = :description
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'department_id' => $data['department_id'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM positions WHERE id = :id'
        );

        return $stmt->execute(['id' => $id]);
    }
}
