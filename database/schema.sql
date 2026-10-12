
CREATE DATABASE IF NOT EXISTS employee_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE employee_management;

CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE positions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_positions_department
        FOREIGN KEY (department_id)
        REFERENCES departments(id)
        ON DELETE RESTRICT,

    UNIQUE KEY unique_position_department (department_id, name),
    UNIQUE KEY unique_position_department_id (id, department_id)
);

CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(25) NULL,
    gender ENUM('male', 'female', 'other') NULL,
    birth_date DATE NULL,
    hire_date DATE NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    employment_status ENUM(
        'active', 'inactive', 'on_leave', 'resigned'
    ) NOT NULL DEFAULT 'active',
    salary DECIMAL(15,2) UNSIGNED NOT NULL DEFAULT 0,
    address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_employees_department
        FOREIGN KEY (department_id)
        REFERENCES departments(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_employees_position_department
        FOREIGN KEY (position_id, department_id)
        REFERENCES positions(id, department_id)
        ON DELETE RESTRICT,

    INDEX idx_employees_name (full_name),
    INDEX idx_employees_status (employment_status),
    INDEX idx_employees_hire_date (hire_date)
);

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr') NOT NULL DEFAULT 'hr',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO departments (name, description) VALUES
('IT', 'Teknologi informasi'),
('HRD', 'Sumber daya manusia'),
('Finance', 'Keuangan'),
('Marketing', 'Pemasaran');

INSERT INTO positions (department_id, name, description) VALUES
(1, 'Web Developer', 'Mengembangkan aplikasi web'),
(1, 'System Administrator', 'Mengelola sistem dan server'),
(2, 'HR Staff', 'Mengelola administrasi karyawan'),
(3, 'Accountant', 'Mengelola pencatatan keuangan'),
(4, 'Marketing Staff', 'Menjalankan aktivitas pemasaran');
