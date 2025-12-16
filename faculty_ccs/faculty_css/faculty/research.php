<?php
$page_title = 'Research';
require_once 'header.php';
require_once '../config/database.php';
require_once '../config/notifications.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_research'])) {
    $faculty_id = $_SESSION['faculty_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $status = 'pending';

    $stmt = $conn->prepare("INSERT INTO research (faculty_id, title, description, category, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $faculty_id, $title, $description, $category, $status);
    
    if ($stmt->execute()) {
        $research_id = $conn->insert_id;
        
        // Get faculty name
        $faculty = $conn->query("SELECT name FROM faculty WHERE faculty_id = $faculty_id")->fetch_assoc();
        $faculty_name = $faculty['name'];
        
        // Notify all admins
        notifyResearchSubmitted($conn, $faculty_name, $title, $research_id);
        
        $message = '<div class="alert alert-success">✓ Research submitted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error submitting research: ' . $conn->error . '</div>';
    }
}

// Get faculty's research
$faculty_id = $_SESSION['faculty_id'];
$research_list = $conn->query("SELECT * FROM research WHERE faculty_id = $faculty_id ORDER BY submission_date DESC");
?>

<style>
    .research-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
    }

    .research-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .btn {
        padding: var(--spacing-sm) var(--spacing-lg);
        border: none;
        border-radius: var(--radius-md);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: var(--accent);
        color: var(--bg-dark);
    }

    .btn-primary:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
    }

    .form-container {
        background: var(--primary-light);
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

    .form-group {
        margin-bottom: var(--spacing-lg);
    }

    .form-group label {
        display: block;
        margin-bottom: var(--spacing-sm);
        font-weight: 600;
        color: var(--text-primary);
        font-size: 1rem;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--primary);
        color: var(--text-primary);
        font-family: inherit;
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-group select {
        cursor: pointer;
    }

    .form-group select option {
        background-color: var(--primary);
        color: var(--text-primary);
    }

    .form-actions {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-lg);
    }

    .alert {
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid var(--success);
        color: var(--success);
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid var(--danger);
        color: var(--danger);
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
        max-width: 1000px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-color);
        padding-bottom: var(--spacing-md);
    }

    .modal-header h3 {
        font-size: 1.5rem;
        color: var(--text-primary);
    }

    .close-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 2rem;
        cursor: pointer;
        transition: var(--transition);
        line-height: 1;
    }

    .close-btn:hover {
        color: var(--accent);
    }

    .table-wrapper {
        overflow-x: auto;
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

    tr:hover {
        background: var(--primary-dark);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.2);
        color: var(--warning);
    }

    .status-approved {
        background: rgba(16, 185, 129, 0.2);
        color: var(--success);
    }

    .status-declined {
        background: rgba(239, 68, 68, 0.2);
        color: var(--danger);
    }

    .status-revision_requested {
        background: rgba(59, 130, 246, 0.2);
        color: var(--info);
    }

    .no-data {
        text-align: center;
        color: var(--text-secondary);
        padding: var(--spacing-xl);
    }
</style>

<div class="research-header">
    <h2>Research Submissions</h2>
    <button class="btn btn-primary" onclick="openModal()">View My Research</button>
</div>

<?php echo $message; ?>

<div class="form-container">
    <h3>Submit New Research</h3>
    <form method="POST">
        <div class="form-group">
            <label for="title">Research Title *</label>
            <input type="text" id="title" name="title" placeholder="Enter research title" required>
        </div>

        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="Artificial Intelligence">Artificial Intelligence</option>
                <option value="Machine Learning">Machine Learning</option>
                <option value="Data Science">Data Science</option>
                <option value="Cybersecurity">Cybersecurity</option>
                <option value="Software Engineering">Software Engineering</option>
                <option value="Web Development">Web Development</option>
                <option value="Mobile Development">Mobile Development</option>
                <option value="Networking">Networking</option>
                <option value="Database Systems">Database Systems</option>
                <option value="Cloud Computing">Cloud Computing</option>
                <option value="Internet of Things">Internet of Things</option>
                <option value="Blockchain">Blockchain</option>
                <option value="Computer Vision">Computer Vision</option>
                <option value="Natural Language Processing">Natural Language Processing</option>
                <option value="Human-Computer Interaction">Human-Computer Interaction</option>
                <option value="Game Development">Game Development</option>
                <option value="Robotics">Robotics</option>
                <option value="Computer Graphics">Computer Graphics</option>
                <option value="Information Systems">Information Systems</option>
                <option value="Educational Technology">Educational Technology</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Enter research description or abstract"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit_research" class="btn btn-primary">Submit Research</button>
        </div>
    </form>
</div>

<!-- Research List Modal -->
<div id="researchModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>My Research Submissions</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Submission Date</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($research_list && $research_list->num_rows > 0): ?>
                        <?php while ($research = $research_list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($research['title']); ?></td>
                            <td><?php echo htmlspecialchars($research['category']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $research['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $research['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($research['submission_date'])); ?></td>
                            <td><?php echo htmlspecialchars($research['comments'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">No research submissions yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('researchModal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('researchModal').classList.remove('show');
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('researchModal');
        if (e.target === modal) {
            closeModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>

<?php require_once 'footer.php'; ?>