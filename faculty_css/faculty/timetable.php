<?php
$page_title = 'My Timetable';
require_once 'header.php';
require_once '../config/database.php';

$faculty_id = $current_user['faculty_id'];

// Get timetable for this faculty
$timetables_list = $conn->query("SELECT t.*, c.course_code, c.course_name 
                                FROM timetables t 
                                JOIN courses c ON t.course_id = c.course_id 
                                WHERE t.faculty_id = $faculty_id 
                                ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), t.start_time ASC");
?>

<style>
    .timetable-container {
        overflow-x: auto;
    }
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    .timetable-table thead {
        background-color: var(--primary-dark);
    }
    .timetable-table th {
        padding: var(--spacing-md);
        text-align: left;
        font-weight: 600;
        color: var(--accent);
        border-bottom: 2px solid var(--border-color);
    }
    .timetable-table td {
        padding: var(--spacing-md);
        border-bottom: 1px solid var(--border-color);
    }
    .timetable-table tbody tr:hover {
        background-color: var(--primary-dark);
    }
    .day-header {
        background-color: var(--primary-dark);
        font-weight: 600;
        color: var(--accent);
    }
    .time-slot {
        font-weight: 500;
        color: var(--text-primary);
    }
    .course-code {
        color: var(--accent);
        font-weight: 600;
    }
    .empty-state {
        text-align: center;
        padding: var(--spacing-xl);
        color: var(--text-secondary);
    }
</style>

<h3>Your Teaching Schedule</h3>

<?php if ($timetables_list->num_rows === 0): ?>
    <div class="empty-state">
        <p>No timetable entries assigned yet.</p>
    </div>
<?php else: ?>
    <div class="timetable-container">
        <table class="timetable-table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Course</th>
                    <th>Room</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $current_day = '';
                while ($timetable = $timetables_list->fetch_assoc()): 
                    if ($current_day !== $timetable['day_of_week']):
                        $current_day = $timetable['day_of_week'];
                ?>
                <tr>
                    <td class="day-header"><?php echo htmlspecialchars($timetable['day_of_week']); ?></td>
                    <td class="time-slot"><?php echo date('H:i', strtotime($timetable['start_time'])); ?> - <?php echo date('H:i', strtotime($timetable['end_time'])); ?></td>
                    <td>
                        <div class="course-code"><?php echo htmlspecialchars($timetable['course_code']); ?></div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($timetable['course_name']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($timetable['room_number']); ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td></td>
                    <td class="time-slot"><?php echo date('H:i', strtotime($timetable['start_time'])); ?> - <?php echo date('H:i', strtotime($timetable['end_time'])); ?></td>
                    <td>
                        <div class="course-code"><?php echo htmlspecialchars($timetable['course_code']); ?></div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($timetable['course_name']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($timetable['room_number']); ?></td>
                </tr>
                <?php endif; ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
