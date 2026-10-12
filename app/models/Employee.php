<?php

declare(strict_types=1);

final class Employee
{
    public function __construct(private PDO $pdo)
    {
    }

    private function buildConditions(string $search, array $filters): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(
                e.employee_code LIKE :code
                OR e.full_name LIKE :name
                OR e.email LIKE :email
                OR d.name LIKE :department_search
                OR p.name LIKE :position_search
            )';

            $term = '%' . $search . '%';

            $params['code'] = $term;
            $params['name'] = $term;
            $params['email'] = $term;
            $params['department_search'] = $term;
            $params['position_search'] = $term;
        }

        if (!empty($filters['department_id'])) {
            $conditions[] = 'e.department_id = :department_id';
            $params['department_id'] = $filters['department_id'];
        }

        if (!empty($filters['position_id'])) {
            $conditions[] = 'e.position_id = :position_id';
            $params['position_id'] = $filters['position_id'];
        }

        if (!empty($filters['employment_status'])) {
            $conditions[] = 'e.employment_status = :employment_status';
            $params['employment_status'] = $filters['employment_status'];
        }

        $where = $conditions
            ? ' WHERE ' . implode(' AND ', $conditions)
            : '';

        return [$where, $params];
    }

    public function all(
        string $search = '',
        array $filters = [],
        int $limit = 10,
        int $offset = 0
    ): array {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        [$where, $params] = $this->buildConditions($search, $filters);

        $sql = 'SELECT e.*, d.name AS department_name,
                       p.name AS position_name
                FROM employees e
                JOIN departments d ON d.id = e.department_id
                JOIN positions p
                  ON p.id = e.position_id
                 AND p.department_id = e.department_id'
                . $where .
                ' ORDER BY e.id DESC
                  LIMIT ' . $limit . ' OFFSET ' . $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countAll(
        string $search = '',
        array $filters = []
    ): int {
        [$where, $params] = $this->buildConditions($search, $filters);

        $sql = 'SELECT COUNT(*)
                FROM employees e
                JOIN departments d ON d.id = e.department_id
                JOIN positions p
                  ON p.id = e.position_id
                 AND p.department_id = e.department_id'
                . $where;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, d.name AS department_name,
                    p.name AS position_name
             FROM employees e
             JOIN departments d ON d.id = e.department_id
             JOIN positions p
               ON p.id = e.position_id
              AND p.department_id = e.department_id
             WHERE e.id = :id'
        );

        $stmt->execute(['id' => $id]);

        $employee = $stmt->fetch();

        return $employee ?: null;
    }

    public function create(array $data): int
    {
        $this->pdo->beginTransaction();

        try {
            // Kode sementara harus unik.
            $temporaryCode = 'TMP-' . bin2hex(random_bytes(4));

            $sql = "INSERT INTO employees (
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
                    )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'employee_code' => $temporaryCode,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'hire_date' => $data['hire_date'],
                'department_id' => $data['department_id'],
                'position_id' => $data['position_id'],
                'employment_status' => $data['employment_status'],
                'salary' => $data['salary'],
                'address' => $data['address'],
            ]);

            $id = (int) $this->pdo->lastInsertId();

            // Contoh: ID 1 menjadi EMP-0001.
            $employeeCode = 'EMP-' . str_pad(
                (string) $id,
                4,
                '0',
                STR_PAD_LEFT
            );

            $stmt = $this->pdo->prepare(
                'UPDATE employees
                 SET employee_code = :employee_code,
                     updated_at = NOW()
                 WHERE id = :id'
            );

            $stmt->execute([
                'employee_code' => $employeeCode,
                'id' => $id,
            ]);

            $this->pdo->commit();

            return $id;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;

        $stmt = $this->pdo->prepare(
            'UPDATE employees SET
                employee_code = :employee_code,
                full_name = :full_name,
                email = :email,
                phone = :phone,
                gender = :gender,
                birth_date = :birth_date,
                hire_date = :hire_date,
                department_id = :department_id,
                position_id = :position_id,
                employment_status = :employment_status,
                salary = :salary,
                address = :address
             WHERE id = :id'
        );

        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM employees WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
