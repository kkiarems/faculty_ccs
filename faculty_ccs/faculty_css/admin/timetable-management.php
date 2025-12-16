<?php
$page_title = 'Timetable Management';
require_once 'header.php';
require_once '../config/database.php';

$message = '';

// Handle add timetable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $course_id = intval($_POST['course_id']);
    $days = $_POST['days'] ?? []; // Array of selected days
    $start_time = sanitize($_POST['start_time']);
    $end_time = sanitize($_POST['end_time']);
    $room_number = sanitize($_POST['room_number']);
    
    if (empty($days)) {
        $message = '<div class="alert alert-danger">Please select at least one day.</div>';
    } else {
        $success_count = 0;
        $error_count = 0;
        
        // Insert a timetable entry for each selected day
        foreach ($days as $day) {
            $day = sanitize($day);
            
            // Check if this time slot is already taken
            $check = "SELECT * FROM timetables WHERE faculty_id = ? AND day_of_week = ? AND 
                     ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))";
            $stmt = $conn->prepare($check);
            $stmt->bind_param("isssss", $faculty_id, $day, $start_time, $start_time, $end_time, $end_time);
            $stmt->execute();
            $existing = $stmt->get_result();
            
            if ($existing->num_rows > 0) {
                $error_count++;
                continue; // Skip this day if time conflict exists
            }
            
            $insert = "INSERT INTO timetables (faculty_id, course_id, day_of_week, start_time, end_time, room_number, created_by) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);
            $stmt->bind_param("iissssi", $faculty_id, $course_id, $day, $start_time, $end_time, $room_number, $current_user['admin_id']);
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        if ($success_count > 0) {
            $message = '<div class="alert alert-success">✓ Successfully added ' . $success_count . ' timetable entry(ies)!</div>';
        }
        if ($error_count > 0) {
            $message .= '<div class="alert alert-danger">⚠ ' . $error_count . ' entry(ies) skipped due to time conflicts.</div>';
        }
    }
}

// Handle delete timetable
if (isset($_GET['delete'])) {
    $timetable_id = intval($_GET['delete']);
    $delete = "DELETE FROM timetables WHERE timetable_id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $timetable_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Timetable entry deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting timetable</div>';
    }
}

// Get faculty and courses for dropdowns
$faculty_list = $conn->query("SELECT faculty_id, name FROM faculty ORDER BY name ASC");
$courses_list = $conn->query("SELECT course_id, course_code, course_name FROM courses ORDER BY course_code ASC");

// Get all timetables grouped by similar schedules
$timetables_list = $conn->query("SELECT t.*, f.name as faculty_name, c.course_code, c.course_name 
                                FROM timetables t 
                                JOIN faculty f ON t.faculty_id = f.faculty_id 
                                JOIN courses c ON t.course_id = c.course_id 
                                ORDER BY f.name, t.start_time, t.day_of_week ASC");
?>

<style>
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
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    
    .form-group label {
        display: block;
        margin-bottom: var(--spacing-sm);
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .form-group select,
    .form-group input[type="time"],
    .form-group input[type="text"] {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--primary);
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .form-group select:focus,
    .form-group input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    }
    
    /* Multi-select days styling */
    .days-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: var(--spacing-sm);
        margin-top: var(--spacing-sm);
    }
    
    .day-checkbox {
        display: none;
    }
    
    .day-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-sm);
        background: var(--primary);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        text-align: center;
        user-select: none;
    }
    
    .day-label:hover {
        border-color: var(--accent);
        background: var(--primary-dark);
    }
    
    .day-checkbox:checked + .day-label {
        background: var(--accent);
        border-color: var(--accent);
        color: var(--bg-dark);
        font-weight: 600;
    }
    
    .select-all-days {
        margin-top: var(--spacing-sm);
        font-size: 0.85rem;
        color: var(--accent);
        cursor: pointer;
        text-decoration: underline;
    }
    
    .select-all-days:hover {
        color: var(--accent-hover);
    }
    
    .table-container {
        overflow-x: auto;
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
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
    
    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
    }
    
    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: 0.85rem;
    }
    
    .day-badge {
        display: inline-block;
        padding: 4px 8px;
        background: rgba(0, 212, 255, 0.2);
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        margin-right: 4px;
        color: var(--accent);
    }
    
    .info-box {
        background: rgba(0, 212, 255, 0.1);
        border-left: 4px solid var(--accent);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
        font-size: 0.9rem;
        color: var(--text-secondary);
    }
</style>

<?php echo $message; ?>

<div class="info-box">
    <strong>Tip:</strong> Select multiple days for the same time slot to quickly add recurring schedules! 
    For example, if a faculty teaches Tuesday & Friday at 1:00-2:30 PM, just select both days at once.
</div>

<div class="form-container">
    <h3>Add Timetable Entry</h3>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="faculty_id">Faculty Member *</label>
                <select id="faculty_id" name="faculty_id" required>
                    <option value="">Select Faculty</option>
                    <?php 
                    $faculty_list->data_seek(0);
                    while ($faculty = $faculty_list->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $faculty['faculty_id']; ?>"><?php echo htmlspecialchars($faculty['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="course_id">Course *</label>
                <select id="course_id" name="course_id" required>
                    <option value="">Select Course</option>
                    <?php 
                    $courses_list->data_seek(0);
                    while ($course = $courses_list->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $course['course_id']; ?>">
                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="start_time">Start Time *</label>
                <input type="time" id="start_time" name="start_time" required>
            </div>
            
            <div class="form-group">
                <label for="end_time">End Time *</label>
                <input type="time" id="end_time" name="end_time" required>
            </div>
            
            <div class="form-group">
                <label for="room_number">Room Number *</label>
                <input type="text" id="room_number" name="room_number" placeholder="e.g., A101" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Days of Week * (Select one or more)</label>
            <div class="days-selector">
                <div>
                    <input type="checkbox" id="monday" name="days[]" value="Monday" class="day-checkbox">
                    <label for="monday" class="day-label">Monday</label>
                </div>
                <div>
                    <input type="checkbox" id="tuesday" name="days[]" value="Tuesday" class="day-checkbox">
                    <label for="tuesday" class="day-label">Tuesday</label>
                </div>
                <div>
                    <input type="checkbox" id="wednesday" name="days[]" value="Wednesday" class="day-checkbox">
                    <label for="wednesday" class="day-label">Wednesday</label>
                </div>
                <div>
                    <input type="checkbox" id="thursday" name="days[]" value="Thursday" class="day-checkbox">
                    <label for="thursday" class="day-label">Thursday</label>
                </div>
                <div>
                    <input type="checkbox" id="friday" name="days[]" value="Friday" class="day-checkbox">
                    <label for="friday" class="day-label">Friday</label>
                </div>
                <div>
                    <input type="checkbox" id="saturday" name="days[]" value="Saturday" class="day-checkbox">
                    <label for="saturday" class="day-label">Saturday</label>
                </div>
            </div>
            <span class="select-all-days" onclick="toggleAllDays()">Select All Days</span>
        </div>
        
        <button type="submit" name="add_timetable" class="btn btn-primary" style="margin-top: var(--spacing-lg);">
            Add to Timetable
        </button>
    </form>
</div>

<h3 style="margin-bottom: var(--spacing-lg);">Current Timetable</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Faculty</th>
                <th>Course</th>
                <th>Day</th>
                <th>Time</th>
                <th>Room</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($timetables_list && $timetables_list->num_rows > 0):
                while ($timetable = $timetables_list->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo htmlspecialchars($timetable['faculty_name']); ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($timetable['course_code']); ?></strong><br>
                    <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($timetable['course_name']); ?></small>
                </td>
                <td>
                    <span class="day-badge"><?php echo htmlspecialchars($timetable['day_of_week']); ?></span>
                </td>
                <td>
                    <?php 
                    echo date('g:i A', strtotime($timetable['start_time'])); 
                    echo ' - ';
                    echo date('g:i A', strtotime($timetable['end_time'])); 
                    ?>
                </td>
                <td><?php echo htmlspecialchars($timetable['room_number']); ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="?delete=<?php echo $timetable['timetable_id']; ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to delete this timetable entry?');">
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
                    No timetable entries yet. Add your first entry above!
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Toggle all days selection
let allSelected = false;

function toggleAllDays() {
    const checkboxes = document.querySelectorAll('.day-checkbox');
    allSelected = !allSelected;
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = allSelected;
    });
    
    document.querySelector('.select-all-days').textContent = allSelected ? 'Deselect All Days' : 'Select All Days';
}

// Validate form before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const checkedDays = document.querySelectorAll('.day-checkbox:checked');
    
    if (checkedDays.length === 0) {
        e.preventDefault();
        alert('Please select at least one day of the week!');
        return false;
    }
    
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (startTime && endTime && startTime >= endTime) {
        e.preventDefault();
        alert('End time must be after start time!');
        return false;
    }
});
</script>

<?php require_once 'footer.php'; ?>