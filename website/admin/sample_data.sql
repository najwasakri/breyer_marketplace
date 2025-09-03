-- Sample tables untuk test auto refresh functionality

-- Create courses table if not exists
CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create students table if not exists  
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    student_ic VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table if not exists
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    course_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

-- Create inventory table if not exists
CREATE TABLE IF NOT EXISTS inventory (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    item_code VARCHAR(20),
    quantity INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample courses
INSERT IGNORE INTO courses (course_id, course_name, course_code, description, price) VALUES
(1, 'Automotive Technology', 'AT001', 'Basic automotive repair and maintenance', 1500.00),
(2, 'Culinary Arts', 'CA001', 'Professional cooking and food preparation', 1200.00),
(3, 'Electrical Installation', 'EI001', 'Electrical systems and wiring', 1800.00),
(4, 'Computer Science', 'CS001', 'Programming and software development', 2000.00),
(5, 'Food & Beverage Service', 'FBS001', 'Restaurant and hospitality service', 1000.00);

-- Insert sample students
INSERT IGNORE INTO students (student_id, student_name, student_ic, email, phone) VALUES
(1, 'Ahmad bin Ali', '001122334455', 'ahmad@email.com', '012-3456789'),
(2, 'Siti Nurhaliza', '001122334456', 'siti@email.com', '012-3456790'),
(3, 'Muhammad Hakim', '001122334457', 'hakim@email.com', '012-3456791'),
(4, 'Nur Aisyah', '001122334458', 'aisyah@email.com', '012-3456792'),
(5, 'Zul Ariffin', '001122334459', 'zul@email.com', '012-3456793');

-- Insert sample orders
INSERT IGNORE INTO orders (order_id, student_id, course_id, total_amount, status) VALUES
(1, 1, 1, 1500.00, 'completed'),
(2, 2, 2, 1200.00, 'paid'),
(3, 3, 3, 1800.00, 'completed'),
(4, 4, 4, 2000.00, 'pending'),
(5, 5, 5, 1000.00, 'paid');

-- Insert sample inventory
INSERT IGNORE INTO inventory (item_id, item_name, item_code, quantity, price) VALUES
(1, 'Automotive Tools Set', 'ATS001', 15, 500.00),
(2, 'Cooking Equipment', 'CE001', 20, 300.00),
(3, 'Electrical Components', 'EC001', 50, 50.00),
(4, 'Computer Hardware', 'CH001', 10, 1000.00),
(5, 'Kitchen Utensils', 'KU001', 30, 100.00),
(6, 'Safety Equipment', 'SE001', 25, 200.00),
(7, 'Learning Materials', 'LM001', 100, 25.00);
