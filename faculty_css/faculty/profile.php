<?php

$current_user = $_SESSION['current_user'] ?? [
    'faculty_id' => 0,
    'name' => 'User',
    'email' => '',
    'contact' => '',
    'position' => '',
    'department' => '',
    'education_degree' => '',
    'education_institution' => '',
    'education_year' => ''
];

$page_title = 'My Profile';

require_once '../config/database.php';
require_once 'header.php';

$message = '';

function fetchCompleteUserData($faculty_id, $conn) {
    $user_data = [];
    
    $faculty_query = "SELECT * FROM faculty WHERE faculty_id = ?";
    $stmt = $conn->prepare($faculty_query);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    }
    
    $edu_query = "SELECT degree, institution, graduation_year FROM educational_information WHERE faculty_id = ?";
    $edu_stmt = $conn->prepare($edu_query);
    $edu_stmt->bind_param("i", $faculty_id);
    $edu_stmt->execute();
    $edu_result = $edu_stmt->get_result();
    
    if ($edu_result->num_rows > 0) {
        $edu_data = $edu_result->fetch_assoc();
        $user_data['education_degree'] = $edu_data['degree'] ?? '';
        $user_data['education_institution'] = $edu_data['institution'] ?? '';
        $user_data['education_year'] = $edu_data['graduation_year'] ?? '';
    } else {
        $user_data['education_degree'] = '';
        $user_data['education_institution'] = '';
        $user_data['education_year'] = '';
    }
    
    return $user_data;
}

if (isset($_SESSION['current_user']['faculty_id'])) {
    $current_user = fetchCompleteUserData($_SESSION['current_user']['faculty_id'], $conn);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $contact = sanitize($_POST['contact']);
    $position = sanitize($_POST['position']);
    $department = sanitize($_POST['department']);
    $education_degree = sanitize($_POST['education_degree']);
    $education_institution = sanitize($_POST['education_institution']);
    $education_year = sanitize($_POST['education_year']);

    $update = "UPDATE faculty SET name=?, email=?, contact=?, position=?, department=? WHERE faculty_id=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("sssssi", $name, $email, $contact, $position, $department, $current_user['faculty_id']);

    if ($stmt->execute()) {
        $check_edu = "SELECT education_id FROM educational_information WHERE faculty_id=?";
        $check_stmt = $conn->prepare($check_edu);
        $check_stmt->bind_param("i", $current_user['faculty_id']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $update_edu = "UPDATE educational_information SET degree=?, institution=?, graduation_year=? WHERE faculty_id=?";
            $edu_stmt = $conn->prepare($update_edu);
            $edu_stmt->bind_param("sssi", $education_degree, $education_institution, $education_year, $current_user['faculty_id']);
            $edu_stmt->execute();
        } else {
            $insert_edu = "INSERT INTO educational_information (faculty_id, degree, institution, graduation_year) VALUES (?, ?, ?, ?)";
            $edu_stmt = $conn->prepare($insert_edu);
            $edu_stmt->bind_param("isss", $current_user['faculty_id'], $education_degree, $education_institution, $education_year);
            $edu_stmt->execute();
        }

        $message = '<div class="success-message">Profile updated successfully!</div>';
        $current_user = fetchCompleteUserData($current_user['faculty_id'], $conn);
    } else {
        $message = '<div class="error-message">Error updating profile</div>';
    }
}

?>

<style>

:root {
    --bg-primary: #0f172a;
    --bg-secondary: #1a2a3a;
    --bg-tertiary: #1e3a5f;
    --border-color: #2a4a7c;
    --text-primary: #ffffff;
    --text-secondary: #a0aec0;
    --text-muted: #718096;
    --accent-cyan: #00d9ff;
    --accent-blue: #3b82f6;
    --success-bg: #1e4620;
    --success-text: #86efac;
    --error-bg: #4a1f1f;
    --error-text: #fca5a5;
}

body {
    background-color: var(--bg-primary);
    color: var(--text-primary);
}

.success-message {
    background-color: var(--success-bg);
    color: var(--success-text);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
    font-weight: 500;
    border: 1px solid rgba(134, 239, 172, 0.2);
}

.error-message {
    background-color: var(--error-bg);
    color: var(--error-text);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
    font-weight: 500;
    border: 1px solid rgba(252, 165, 165, 0.2);
}

.profile-wrapper {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    min-height: 100vh;
}

.profile-card {
    background-color: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, var(--accent-cyan) 0%, var(--accent-blue) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 3rem;
    color: #ffffff;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(0, 217, 255, 0.2);
}

.profile-card h2 {
    text-align: center;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.profile-card .username {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.profile-card .bio {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.profile-stats {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--text-secondary);
}

.stat-item strong {
    color: var(--accent-cyan);
    font-weight: 600;
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.info-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-cyan);
}


.profile-content {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 4rem);
}

.tabs-header {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    padding: 0 2rem;
    position: sticky;
    top: 0;
    background-color: var(--bg-secondary);
    z-index: 10;
    flex-shrink: 0;
}

.tab-button {
    padding: 1.25rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    color: var(--text-muted);
    position: relative;
    transition: color 0.2s;
}

.tab-button:hover {
    color: var(--text-secondary);
}

.tab-button.active {
    color: var(--accent-cyan);
}

.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background-color: var(--accent-cyan);
}

.tab-content {
    display: none;
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

.tab-content.active {
    display: block;
}

.content-section {
    margin-bottom: 2rem;
}

.content-section:last-child {
    margin-bottom: 0;
}

.content-section h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.content-section p {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.education-item {
    border-left: 3px solid var(--accent-cyan);
    padding-left: 1rem;
    margin-bottom: 1.5rem;
}

.education-item h4 {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.education-item p {
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.edit-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    color: var(--text-primary);
    font-weight: 500;
    font-size: 0.95rem;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    font-size: 1rem;
    box-sizing: border-box;
    transition: border-color 0.2s;
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
}

.form-group input::placeholder {
    color: var(--text-muted);
}

.form-group input:focus {
    outline: none;
    border-color: var(--accent-cyan);
    box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
}

.education-fields-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
    background-color: var(--bg-tertiary);
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.education-fields-group > label {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.education-fields-group .form-group label {
    font-weight: 500;
    font-size: 0.9rem;
}

.form-button {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    background-color: var(--accent-blue);
    color: var(--text-primary);
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 1rem;
}

.form-button:hover {
    background-color: var(--accent-cyan);
    color: var(--bg-primary);
}
.overview-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 1.5rem;
    background-color: var(--bg-tertiary);
}

.overview-section-content h4 {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 500;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.overview-section-content p {
    font-size: 1rem;
    color: var(--text-primary);
    font-weight: 500;
}

.overview-section-edit {
    color: var(--accent-cyan);
    font-size: 0.9rem;
    cursor: pointer;
    text-decoration: none;
    transition: color 0.2s;
}

.overview-section-edit:hover {
    color: var(--text-primary);
}

.overview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .profile-wrapper {
        grid-template-columns: 1fr;
    }
    
    .profile-card {
        position: static;
    }
    
    .tabs-header {
        padding: 0 1rem;
    }
    
    .tab-button {
        padding: 1rem;
        font-size: 0.9rem;
    }
    
    .tab-content {
        padding: 1.5rem;
    }

    .overview-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="profile-wrapper">
  
    <div class="profile-card">
        <div class="profile-avatar"><?php echo strtoupper(substr($current_user['name'], 0, 1)); ?></div>
        <h2><?php echo htmlspecialchars(strtoupper($current_user['name'])); ?></h2>
        <div class="username">@<?php echo htmlspecialchars(strtolower(str_replace(' ', '', $current_user['name']))); ?></div>
        <div class="bio">Faculty Member</div>
        
        <div class="profile-stats">
            <div class="stat-item">
                <span>Courses</span>
                <strong>0</strong>
            </div>
            <div class="stat-item">
                <span>Research</span>
                <strong>0</strong>
            </div>
            <div class="stat-item">
                <span>Experience</span>
                <strong>2 yrs</strong>
            </div>
        </div>
        
        <div class="profile-info">
            <div class="info-item">
                <div class="info-icon">📍</div>
                <span><?php echo htmlspecialchars($current_user['department'] ?: 'Department'); ?></span>
            </div>
            <div class="info-item">
                <div class="info-icon">✉️</div>
                <span><?php echo htmlspecialchars($current_user['email']); ?></span>
            </div>
            <div class="info-item">
                <div class="info-icon">📱</div>
                <span><?php echo htmlspecialchars($current_user['contact'] ?: 'Contact'); ?></span>
            </div>
        </div>
    </div>

    
    <div class="profile-content">
        <div class="tabs-header">
            <button class="tab-button active" onclick="switchTab('overview')">Overview</button>
            <button class="tab-button" onclick="switchTab('education')">Education</button>
            <button class="tab-button" onclick="switchTab('edit')">Edit</button>
        </div>

        <!-- Overview Tab -->
        <div id="overview" class="tab-content active">
            <?php echo $message; ?>
            
            <div class="overview-section">
                <div class="overview-section-content">
                    <h4>Position</h4>
                    <p><?php echo htmlspecialchars($current_user['position'] ?: 'Not specified'); ?></p>
                </div>
                <a class="overview-section-edit" onclick="switchTab('edit')">Edit ✎</a>
            </div>

            <div class="overview-section">
                <div class="overview-section-content">
                    <h4>Department</h4>
                    <p><?php echo htmlspecialchars($current_user['department'] ?: 'Not specified'); ?></p>
                </div>
                <a class="overview-section-edit" onclick="switchTab('edit')">Edit ✎</a>
            </div>

            <div class="overview-section">
                <div class="overview-section-content">
                    <h4>Educational Background</h4>
                    <p><?php echo htmlspecialchars($current_user['education_degree'] ?: 'Not specified'); ?></p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;"><?php echo htmlspecialchars($current_user['education_institution'] ?: ''); ?></p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;"><?php echo htmlspecialchars($current_user['education_year'] ? 'Graduated: ' . $current_user['education_year'] : ''); ?></p>
                </div>
                <a class="overview-section-edit" onclick="switchTab('edit')">Edit ✎</a>
            </div>
        </div>

        <!-- Education Tab -->
        <div id="education" class="tab-content">
            <div class="content-section">
                <h3>Educational Background</h3>
                <div class="education-item">
                    <h4><?php echo htmlspecialchars($current_user['education_degree'] ?: 'Degree not specified'); ?></h4>
                    <p><?php echo htmlspecialchars($current_user['education_institution'] ?: 'Institution not specified'); ?></p>
                    <p><?php echo htmlspecialchars($current_user['education_year'] ? 'Graduated: ' . $current_user['education_year'] : 'Graduation year not specified'); ?></p>
                </div>
            </div>
            <div class="content-section">
                <h3>Areas of Specialization</h3>
                <p>Not specified</p>
            </div>
            <div class="content-section">
                <h3>Professional Certifications</h3>
                <p>Not specified</p>
            </div>
            <div class="content-section">
                <h3>Teaching Experience</h3>
                <p>2 years</p>
            </div>
        </div>

        <!-- Edit Tab -->
        <div id="edit" class="tab-content">
            <?php echo $message; ?>
            <form method="POST" class="edit-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($current_user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($current_user['contact']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" value="<?php echo htmlspecialchars($current_user['position']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" value="<?php echo htmlspecialchars($current_user['department']); ?>">
                </div>
                
                <div class="education-fields-group">
                    <label>Educational Background</label>
                    
                    <div class="form-group">
                        <label>Degree</label>
                        <input type="text" name="education_degree" placeholder="e.g., Bachelor's in Computer Science" value="<?php echo htmlspecialchars($current_user['education_degree'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Institution</label>
                        <input type="text" name="education_institution" placeholder="e.g., Western Mindanao State University" value="<?php echo htmlspecialchars($current_user['education_institution'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Graduation Year</label>
                        <input type="text" name="education_year" placeholder="e.g., 2025" value="<?php echo htmlspecialchars($current_user['education_year'] ?? ''); ?>">
                    </div>
                </div>
                
                <button type="submit" name="update_profile" class="form-button">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        const buttons = document.querySelectorAll('.tab-button');
        buttons.forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }
</script>

<?php require_once 'footer.php'; ?>