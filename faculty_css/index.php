<?php
require_once 'config/database.php';
require_once 'config/session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: faculty/dashboard.php");
    }
    exit();
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    
    // First, check if email exists in admins table
    $admin_query = "SELECT * FROM admins WHERE email = ?";
    $stmt = $conn->prepare($admin_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin_result = $stmt->get_result();
    
    if ($admin_result->num_rows === 1) {
        // User is an admin
        $user = $admin_result->fetch_assoc();
        if (verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['admin_id'];
            $_SESSION['admin_id'] = $user['admin_id'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_name'] = $user['name'];
            
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $login_error = "Invalid email or password";
        }
    } else {
        // Check if email exists in faculty table
        $faculty_query = "SELECT * FROM faculty WHERE email = ?";
        $stmt = $conn->prepare($faculty_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $faculty_result = $stmt->get_result();
        
        if ($faculty_result->num_rows === 1) {
            // User is faculty
            $user = $faculty_result->fetch_assoc();
            if (verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['faculty_id'];
                $_SESSION['faculty_id'] = $user['faculty_id'];
                $_SESSION['user_type'] = 'faculty';
                $_SESSION['user_name'] = $user['name'];
                
                // Required by some faculty pages
                $_SESSION['current_user'] = [
                    'faculty_id' => $user['faculty_id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ];
                
                header("Location: faculty/dashboard.php");
                exit();
            } else {
                $login_error = "Invalid email or password";
            }
        } else {
            $login_error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Information System - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background-color: var(--primary-light);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: var(--accent);
            font-size: 1.75rem;
            margin-bottom: 10px;
        }
        .login-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background-color: var(--primary);
            color: var(--text-primary);
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        .btn-login:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
        }
        .alert {
            padding: 12px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }
        .login-info {
            margin-top: 20px;
            padding: 12px;
            background-color: rgba(0, 212, 255, 0.1);
            border-left: 4px solid var(--accent);
            border-radius: var(--radius-md);
            text-align: center;
        }
        .login-info p {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>FMS</h1>
                <p>Faculty Information System</p>
            </div>
            
            <?php if (!empty($login_error)): ?>
                <div class="alert"><?php echo $login_error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>