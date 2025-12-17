<?php
$page_title = 'My Profile';
require_once 'header.php';
require_once '../config/database.php';

$message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $position = sanitize($_POST['position']);
    $department = sanitize($_POST['department']);
    
    $update = "UPDATE admins SET name = ?, email = ?, position = ?, department = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssssi", $name, $email, $position, $department, $current_user['admin_id']);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Profile updated successfully!</div>';
        $current_user = getCurrentUser($conn);
    } else {
        $message = '<div class="alert alert-danger">Error updating profile</div>';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = sanitize($_POST['old_password']);
    $new_password = sanitize($_POST['new_password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    
    if ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-danger">Passwords do not match</div>';
    } elseif (!verifyPassword($old_password, $current_user['password'])) {
        $message = '<div class="alert alert-danger">Old password is incorrect</div>';
    } else {
        $hashed_password = hashPassword($new_password);
        $update = "UPDATE admins SET password = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("si", $hashed_password, $current_user['admin_id']);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Password changed successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error changing password</div>';
        }
    }
}
?>

<style>
    .profile-container {
        max-width: 600px;
    }
    .profile-section {
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }
    .profile-section h3 {
        margin-bottom: var(--spacing-lg);
        color: var(--accent);
    }
</style>

<div class="profile-container">
    <?php echo $message; ?>
    
    <div class="profile-section">
        <h3>Personal Information</h3>
        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($current_user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="position">Position</label>
                <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($current_user['position']); ?>">
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($current_user['department']); ?>">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
    
    <div class="profile-section">
        <h3>Change Password</h3>
        <form method="POST">
            <div class="form-group">
                <label for="old_password">Current Password</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
