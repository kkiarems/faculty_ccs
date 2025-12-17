<?php
// Setup script for Faculty Information System
require_once 'config/database.php';

$setup_complete = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $queries = [
        // Create admins table
        "CREATE TABLE IF NOT EXISTS admins (
            admin_id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            position VARCHAR(100),
            department VARCHAR(100),
            profile_photo VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Create faculty table
        "CREATE TABLE IF NOT EXISTS faculty (
            faculty_id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            position VARCHAR(100),
            department VARCHAR(100),
            contact VARCHAR(20),
            profile_photo VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Create courses table
        "CREATE TABLE IF NOT EXISTS courses (
            course_id INT PRIMARY KEY AUTO_INCREMENT,
            course_code VARCHAR(50) UNIQUE NOT NULL,
            course_name VARCHAR(150) NOT NULL,
            description TEXT,
            units INT,
            semester INT,
            year_level INT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admins(admin_id)
        )",
        
        // Create course assignments table
        "CREATE TABLE IF NOT EXISTS course_assignments (
            assignment_id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            faculty_id INT NOT NULL,
            assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(course_id),
            FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
        )",
        
        // Create research table
        "CREATE TABLE IF NOT EXISTS research (
            research_id INT PRIMARY KEY AUTO_INCREMENT,
            faculty_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(50),
            status ENUM('pending', 'approved', 'declined', 'revision_requested') DEFAULT 'pending',
            submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approval_date TIMESTAMP NULL,
            approved_by INT,
            comments TEXT,
            FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id),
            FOREIGN KEY (approved_by) REFERENCES admins(admin_id)
        )",
        
        // Create leave table
        "CREATE TABLE IF NOT EXISTS leaves (
            leave_id INT PRIMARY KEY AUTO_INCREMENT,
            faculty_id INT NOT NULL,
            leave_type VARCHAR(50),
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            reason TEXT,
            status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
            admin_comments TEXT,
            requested_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approval_date TIMESTAMP NULL,
            approved_by INT,
            FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id),
            FOREIGN KEY (approved_by) REFERENCES admins(admin_id)
        )",
        
        // Create documents table
        "CREATE TABLE IF NOT EXISTS documents (
            document_id INT PRIMARY KEY AUTO_INCREMENT,
            faculty_id INT,
            document_name VARCHAR(255) NOT NULL,
            document_type VARCHAR(50),
            file_path VARCHAR(255),
            category VARCHAR(100),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            uploaded_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approval_date TIMESTAMP NULL,
            approved_by INT,
            is_template BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id),
            FOREIGN KEY (approved_by) REFERENCES admins(admin_id)
        )",
        
        // Create timetable table
        "CREATE TABLE IF NOT EXISTS timetables (
            timetable_id INT PRIMARY KEY AUTO_INCREMENT,
            faculty_id INT NOT NULL,
            course_id INT NOT NULL,
            day_of_week VARCHAR(20),
            start_time TIME,
            end_time TIME,
            room_number VARCHAR(50),
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id),
            FOREIGN KEY (course_id) REFERENCES courses(course_id),
            FOREIGN KEY (created_by) REFERENCES admins(admin_id)
        )",
        
        // Create leave balance table
        "CREATE TABLE IF NOT EXISTS leave_balance (
            balance_id INT PRIMARY KEY AUTO_INCREMENT,
            faculty_id INT NOT NULL UNIQUE,
            vacation_days INT DEFAULT 15,
            sick_days INT DEFAULT 10,
            emergency_days INT DEFAULT 5,
            year INT DEFAULT YEAR(CURDATE()),
            FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
        )",
        
        // Create indexes
        "CREATE INDEX idx_faculty_email ON faculty(email)",
        "CREATE INDEX idx_admin_email ON admins(email)",
        "CREATE INDEX idx_research_faculty ON research(faculty_id)",
        "CREATE INDEX idx_research_status ON research(status)",
        "CREATE INDEX idx_leave_faculty ON leaves(faculty_id)",
        "CREATE INDEX idx_leave_status ON leaves(status)",
        "CREATE INDEX idx_document_faculty ON documents(faculty_id)",
        "CREATE INDEX idx_timetable_faculty ON timetables(faculty_id)"
    ];
    
    $error_occurred = false;
    foreach ($queries as $query) {
        if (!empty(trim($query))) {
            if (!$conn->query($query)) {
                $message = "Error creating tables: " . $conn->error;
                $error_occurred = true;
                break;
            }
        }
    }
    
    if (!$error_occurred) {
        // Create default admin account
        $admin_name = sanitize($_POST['admin_name']);
        $admin_email = sanitize($_POST['admin_email']);
        $admin_password = hashPassword(sanitize($_POST['admin_password']));
        
        $insert_admin = "INSERT INTO admins (name, email, password, position, department) 
                        VALUES (?, ?, ?, 'System Administrator', 'Administration')";
        $stmt = $conn->prepare($insert_admin);
        $stmt->bind_param("sss", $admin_name, $admin_email, $admin_password);
        
        if ($stmt->execute()) {
            $setup_complete = true;
            $message = "Setup completed successfully! You can now login with your admin account.";
        } else {
            $message = "Error creating admin account: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Information System - Setup</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .setup-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            background-color: var(--primary-light);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-header h1 {
            color: var(--accent);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>Faculty Information System</h1>
            <p>Initial Setup</p>
        </div>
        
        <?php if ($setup_complete): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
            <p style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn btn-primary">Go to Login</a>
            </p>
        <?php else: ?>
            <?php if (!empty($message)): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="admin_name">Admin Name</label>
                    <input type="text" id="admin_name" name="admin_name" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Complete Setup</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
