<?php
$page_title = 'Admin Management';
require_once 'header.php';
require_once '../config/database.php';

$message = '';

// Handle add admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = hashPassword(sanitize($_POST['password']));
    $position = sanitize($_POST['position']);
    $department = sanitize($_POST['department']);
    
    $insert = "INSERT INTO admins (name, email, password, position, department) 
               VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("sssss", $name, $email, $password, $position, $department);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Admin user added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding admin: ' . $conn->error . '</div>';
    }
}

// Handle delete admin
if (isset($_GET['delete'])) {
    $admin_id = intval($_GET['delete']);
    if ($admin_id !== $current_user['admin_id']) {
        $delete = "DELETE FROM admins WHERE admin_id = ?";
        $stmt = $conn->prepare($delete);
        $stmt->bind_param("i", $admin_id);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Admin user deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting admin</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">You cannot delete your own account</div>';
    }
}

// Get all admins
$admin_list = $conn->query("SELECT * FROM admins ORDER BY name ASC");
?>

<style>
    .form-container {
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
    }
    .table-container {
        overflow-x: auto;
    }
    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
    }
    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: 0.85rem;
    }
</style>

<?php echo $message; ?>

<div class="form-container">
    <h3>Add New Admin User</h3>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="position">Position</label>
                <input type="text" id="position" name="position" placeholder="e.g., Department Head">
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" placeholder="e.g., Administration">
            </div>
        </div>
        <button type="submit" name="add_admin" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Add Admin User</button>
    </form>
</div>

<h3>Admin Users List</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Position</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($admin = $admin_list->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($admin['name']); ?></td>
                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                <td><?php echo htmlspecialchars($admin['position']); ?></td>
                <td><?php echo htmlspecialchars($admin['department']); ?></td>
                <td>
                    <div class="action-buttons">
                    <?php if (isset($admin['admin_id'], $current_user['admin_id']) && $admin['admin_id'] !== $current_user['admin_id']): ?>
                        <a href="?delete=<?php echo $admin['admin_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                        <?php else: ?>
                        <span class="text-muted">(Your Account)</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
