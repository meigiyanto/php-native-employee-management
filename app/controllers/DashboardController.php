<?php

declare(strict_types=1);

final class DashboardController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function index(): array
    {
        $employees = (int) $this->pdo
            ->query('SELECT COUNT(*) FROM employees')
            ->fetchColumn();

        $activeEmployees = (int) $this->pdo
            ->query(
                "SELECT COUNT(*) FROM employees
                 WHERE employment_status = 'active'"
            )
            ->fetchColumn();

        $departments = (int) $this->pdo
            ->query('SELECT COUNT(*) FROM departments')
            ->fetchColumn();

        $positions = (int) $this->pdo
            ->query('SELECT COUNT(*) FROM positions')
            ->fetchColumn();

        $statement = $this->pdo->query(
            'SELECT e.employee_code, e.full_name, e.email,
                    e.employment_status, e.hire_date,
                    d.name AS department_name,
                    p.name AS position_name
             FROM employees e
             INNER JOIN departments d
                ON d.id = e.department_id
             INNER JOIN positions p
                ON p.id = e.position_id
               AND p.department_id = e.department_id
             ORDER BY e.created_at DESC
             LIMIT 5'
        );

        return [
            'totalEmployees' => $employees,
            'activeEmployees' => $activeEmployees,
            'totalDepartments' => $departments,
            'totalPositions' => $positions,
            'recentEmployees' => $statement->fetchAll(),
        ];
    }
}
