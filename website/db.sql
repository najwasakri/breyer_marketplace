-- Buang database jika wujud (berhati-hati!)
DROP DATABASE IF EXISTS breyer_marketplace;

-- Cipta database baru
CREATE DATABASE breyer_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Guna database tersebut
USE breyer_marketplace;

-- Jadual users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    ic_number VARCHAR(14) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jadual login_attempts (optional tapi disarankan untuk keselamatan)
CREATE TABLE login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    ic_number VARCHAR(14) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success BOOLEAN NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jadual admin
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    ic_number VARCHAR(14) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Jadual admin_logs
CREATE TABLE admin_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id)
);

CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(50) UNIQUE NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    supplier VARCHAR(255),
    minimum_stock INT DEFAULT 10,
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_item_code (item_code),
    INDEX idx_category (category),
    CHECK (quantity >= 0),
    CHECK (price >= 0)
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    category VARCHAR(100),
    instructor_name VARCHAR(100),
    max_students INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course_name (course_name),
    INDEX idx_category (category),
    CHECK (price >= 0),
    CHECK (max_students > 0)
);

-- Insert default super admin account
INSERT INTO admin (username, password_hash, ic_number, full_name, role) 
VALUES (
    'superadmin', 
    '$2y$10$YOUR_HASHED_PASSWORD',
    '000000000000', -- Replace with actual IC number
    'Super Administrator',
    'super_admin'
);

-- Insert sample courses
INSERT INTO courses (course_name, description, price, duration, category, instructor_name) VALUES
('Kursus Asas Memasak', 'Pembelajaran asas teknik memasak', 299.99, '3 bulan', 'Masakan', 'Chef Ahmad'),
('Kursus Jahitan', 'Asas jahitan dan teknik menjahit', 399.99, '6 bulan', 'Kraftangan', 'Puan Aminah'),
('Kursus Komputer', 'Pengenalan kepada Microsoft Office', 199.99, '1 bulan', 'Teknologi', 'En. Rahman');
