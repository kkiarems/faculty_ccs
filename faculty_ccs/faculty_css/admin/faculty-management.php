<?php
$page_title = 'Faculty Management';
require_once 'header.php';
require_once '../config/database.php';

$message = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle add faculty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
    $first_name = sanitize($_POST['first_name']);
    $middle_name = sanitize($_POST['middle_name']);
    $last_name = sanitize($_POST['last_name']);
    $extension_name = sanitize($_POST['extension_name']);
    
    // Combine names for the full name field
    $name_parts = array_filter([$first_name, $middle_name, $last_name, $extension_name]);
    $full_name = implode(' ', $name_parts);
    
    $email = sanitize($_POST['email']);
    $password = hashPassword(sanitize($_POST['password']));
    $position = sanitize($_POST['position']);
    $department = sanitize($_POST['department']);
    $contact = sanitize($_POST['contact']);
    
    $insert = "INSERT INTO faculty (name, email, password, position, department, contact) 
               VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("ssssss", $full_name, $email, $password, $position, $department, $contact);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Faculty member added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding faculty: ' . $conn->error . '</div>';
    }
}

// Handle delete faculty
if (isset($_GET['delete'])) {
    $faculty_id = intval($_GET['delete']);
    $delete = "DELETE FROM faculty WHERE faculty_id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $faculty_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Faculty member deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting faculty</div>';
    }
}

// Get all departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name ASC");

// Search functionality
$search_query = '';
$search_department = '';

if (isset($_GET['search'])) {
    $search_query = sanitize($_GET['search_query'] ?? '');
    $search_department = sanitize($_GET['search_department'] ?? '');
}

// Build query with search filters
$query = "SELECT * FROM faculty WHERE 1=1";
$params = [];
$types = "";

if (!empty($search_query)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR position LIKE ? OR contact LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if (!empty($search_department)) {
    $query .= " AND department = ?";
    $params[] = $search_department;
    $types .= "s";
}

$query .= " ORDER BY name ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $faculty_list = $stmt->get_result();
} else {
    $faculty_list = $conn->query($query);
}
?>

<style>
    .page-header {
        margin-bottom: var(--spacing-xl);
    }
    
    .page-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
    }
    
    .form-container {
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
    }
    
    .form-container h3 {
        font-size: 1.5rem;
        margin-bottom: var(--spacing-lg);
        color: var(--text-primary);
    }
    
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--accent);
        margin-bottom: var(--spacing-md);
        padding-bottom: var(--spacing-sm);
        border-bottom: 2px solid var(--border-color);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    
    .form-row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    
    .form-row-4col {
        display: grid;
        grid-template-columns: 2fr 2fr 2fr 1fr;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }
    
    .search-container {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-dark) 100%);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }
    
    .search-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-md);
    }
    
    .search-header h3 {
        font-size: 1.25rem;
        color: var(--text-primary);
        margin: 0;
    }
    
    .search-form {
        display: grid;
        grid-template-columns: 1fr 200px auto;
        gap: var(--spacing-md);
        align-items: end;
    }
    
    .search-input-group {
        position: relative;
    }
    
    .search-input-group input {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-md) var(--spacing-sm) 40px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--primary);
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .search-input-group input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
        color: var(--text-secondary);
    }
    
    .clear-search {
        background: var(--primary);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: var(--spacing-sm) var(--spacing-lg);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
    }
    
    .clear-search:hover {
        border-color: var(--danger);
        color: var(--danger);
    }
    
    .search-results-info {
        margin-top: var(--spacing-md);
        padding: var(--spacing-sm);
        background: rgba(0, 212, 255, 0.1);
        border-radius: var(--radius-sm);
        color: var(--text-secondary);
        font-size: 0.9rem;
    }
    
    .table-container {
        overflow-x: auto;
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
    }
    
    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
    }
    
    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: 0.85rem;
    }
    
    /* Dropdown styling */
    select {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background-color: var(--primary);
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
        cursor: pointer;
    }
    
    select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    }
    
    select option {
        background-color: var(--primary);
        color: var(--text-primary);
        padding: var(--spacing-sm);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th {
        background: var(--primary-dark);
        color: var(--accent);
        padding: var(--spacing-md);
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--border-color);
    }
    
    td {
        padding: var(--spacing-md);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
    }
    
    tbody tr:hover {
        background: var(--primary-dark);
    }
    
    .optional-label {
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 400;
    }
    
    @media (max-width: 768px) {
        .search-form {
            grid-template-columns: 1fr;
        }
        
        .form-row-4col {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <h2>Faculty Management</h2>
    <p style="color: var(--text-secondary);">Manage faculty members and their information</p>
</div>

<?php echo $message; ?>

<div class="form-container">
    <h3>Add New Faculty Member</h3>
    <form method="POST">
        <!-- Name Section -->
        <div class="form-section-title">Personal Information</div>
        <div class="form-row-4col">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" placeholder="e.g., Juan" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name <span class="optional-label">(Optional)</span></label>
                <input type="text" id="middle_name" name="middle_name" placeholder="e.g., Dela">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" placeholder="e.g., Cruz" required>
            </div>
            <div class="form-group">
                <label for="extension_name">Suffix <span class="optional-label">(Optional)</span></label>
                <input type="text" id="extension_name" name="extension_name" placeholder="e.g., Jr., Sr., III">
            </div>
        </div>
        
        <!-- Contact & Account Section -->
        <div class="form-section-title">Contact & Account Information</div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="e.g., juan.cruz@email.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="text" id="contact" name="contact" placeholder="e.g., +63 912 345 6789">
            </div>
        </div>
        
        <!-- Work Information Section -->
        <div class="form-section-title">Work Information</div>
        <div class="form-row-2col">
            <div class="form-group">
                <label for="position">Position</label>
                <input type="text" id="position" name="position" placeholder="e.g., Assistant Professor">
            </div>
            <div class="form-group">
                <label for="department">Department *</label>
                <select id="department" name="department" required>
                    <option value="">Select Department</option>
                    <?php 
                    $departments->data_seek(0);
                    if ($departments && $departments->num_rows > 0):
                        while ($dept = $departments->fetch_assoc()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php 
                        endwhile;
                    endif;
                    ?>
                </select>
            </div>
        </div>
        
        <button type="submit" name="add_faculty" class="btn btn-primary" style="margin-top: var(--spacing-lg);">
            Add Faculty Member
        </button>
    </form>
</div>

<!-- Search Section -->
<div class="search-container">
    <div class="search-header">
        <h3>Search Faculty</h3>
    </div>
    <form method="GET" class="search-form">
        <div class="search-input-group">
            <span class="search-icon">🔍</span>
            <input type="text" 
                   name="search_query" 
                   placeholder="Search by name, email, position, or contact..." 
                   value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <select name="search_department">
                <option value="">All Departments</option>
                <?php 
                $departments->data_seek(0);
                if ($departments && $departments->num_rows > 0):
                    while ($dept = $departments->fetch_assoc()): 
                ?>
                    <option value="<?php echo htmlspecialchars($dept['department_name']); ?>"
                            <?php echo $search_department === $dept['department_name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['department_name']); ?>
                    </option>
                <?php 
                    endwhile;
                endif;
                ?>
            </select>
        </div>
        
        <div style="display: flex; gap: var(--spacing-sm);">
            <button type="submit" name="search" class="btn btn-primary">Search</button>
            <?php if (!empty($search_query) || !empty($search_department)): ?>
                <a href="faculty-management.php" class="clear-search">✕ Clear</a>
            <?php endif; ?>
        </div>
    </form>
    
    <?php if (!empty($search_query) || !empty($search_department)): ?>
        <div class="search-results-info">
            📊 Showing results for: 
            <?php if (!empty($search_query)): ?>
                <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>
            <?php endif; ?>
            <?php if (!empty($search_department)): ?>
                in <strong><?php echo htmlspecialchars($search_department); ?></strong>
            <?php endif; ?>
            (<?php echo $faculty_list->num_rows; ?> found)
        </div>
    <?php endif; ?>
</div>

<h3 style="margin-bottom: var(--spacing-lg);">Faculty Members List</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Position</th>
                <th>Department</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($faculty_list && $faculty_list->num_rows > 0):
                while ($faculty = $faculty_list->fetch_assoc()): 
            ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($faculty['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($faculty['email']); ?></td>
                <td><?php echo htmlspecialchars($faculty['position']); ?></td>
                <td>
                    <span style="background: rgba(0, 212, 255, 0.2); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                        <?php echo htmlspecialchars($faculty['department']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($faculty['contact']); ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="?delete=<?php echo $faculty['faculty_id']; ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to delete this faculty member?');">
                           Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: var(--spacing-xl); color: var(--text-secondary);">
                    <?php if (!empty($search_query) || !empty($search_department)): ?>
                        No faculty members found matching your search criteria.
                    <?php else: ?>
                        No faculty members found. Add your first faculty member above!
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>