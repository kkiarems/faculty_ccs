<?php
$page_title = 'Course Management';
require_once 'header.php';
require_once '../config/database.php';

$message = '';

// Handle add course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $course_code = sanitize($_POST['course_code']);
    $course_name = sanitize($_POST['course_name']);
    $description = sanitize($_POST['description']);
    $units = intval($_POST['units']);
    $semester = intval($_POST['semester']);
    $year_level = intval($_POST['year_level']);
    
    $insert = "INSERT INTO courses (course_code, course_name, description, units, semester, year_level, created_by) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("sssiiii", $course_code, $course_name, $description, $units, $semester, $year_level, $current_user['admin_id']);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Course added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding course: ' . $conn->error . '</div>';
    }
}

// Handle delete course
if (isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    $delete = "DELETE FROM courses WHERE course_id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $course_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Course deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting course</div>';
    }
}

// Get all courses
$courses_list = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
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
    <h3>Add New Course</h3>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="course_code">Course Code</label>
                <input type="text" id="course_code" name="course_code" placeholder="e.g., CS101" required>
            </div>
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" id="course_name" name="course_name" required>
            </div>
            <div class="form-group">
                <label for="units">Units</label>
                <input type="number" id="units" name="units" min="1" required>
            </div>
            <div class="form-group">
                <label for="semester">Semester</label>
                <input type="number" id="semester" name="semester" min="1" max="2" required>
            </div>
            <div class="form-group">
                <label for="year_level">Year Level</label>
                <input type="number" id="year_level" name="year_level" min="1" max="4" required>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
    </form>
</div>

<h3>Courses List</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Units</th>
                <th>Semester</th>
                <th>Year Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = $courses_list->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                <td><?php echo $course['units']; ?></td>
                <td><?php echo $course['semester']; ?></td>
                <td><?php echo $course['year_level']; ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="?delete=<?php echo $course['course_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
