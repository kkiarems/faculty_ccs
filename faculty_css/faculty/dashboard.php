<?php
$page_title = 'Dashboard';
require_once 'header.php';
require_once '../config/database.php';

// Get statistics for this faculty
$faculty_id = isset($current_user['faculty_id']) ? (int)$current_user['faculty_id'] : 0;

// Research KPIs
$research_count = 0;
$approved_research = 0;
$pending_research = 0;
$rejected_research = 0;

if ($faculty_id > 0) {
    $research_count = $conn->query("SELECT COUNT(*) as count FROM research WHERE faculty_id = $faculty_id")->fetch_assoc()['count'];
    $approved_research = $conn->query("SELECT COUNT(*) as count FROM research WHERE faculty_id = $faculty_id AND status = 'approved'")->fetch_assoc()['count'];
    $pending_research = $conn->query("SELECT COUNT(*) as count FROM research WHERE faculty_id = $faculty_id AND status = 'pending'")->fetch_assoc()['count'];
    $rejected_research = $conn->query("SELECT COUNT(*) as count FROM research WHERE faculty_id = $faculty_id AND status = 'declined'")->fetch_assoc()['count'];
}

// Leave KPIs
$leave_requests = $conn->query("SELECT COUNT(*) as count FROM leaves WHERE faculty_id = $faculty_id")->fetch_assoc()['count'];
$approved_leaves = $conn->query("SELECT COUNT(*) as count FROM leaves WHERE faculty_id = $faculty_id AND status = 'approved'")->fetch_assoc()['count'];
$pending_leaves = $conn->query("SELECT COUNT(*) as count FROM leaves WHERE faculty_id = $faculty_id AND status = 'pending'")->fetch_assoc()['count'];

// Course KPIs
$courses_count = $conn->query("SELECT COUNT(*) as count FROM course_assignments WHERE faculty_id = $faculty_id")->fetch_assoc()['count'];

// Document KPIs
$documents_count = $conn->query("SELECT COUNT(*) as count FROM documents WHERE faculty_id = $faculty_id")->fetch_assoc()['count'];
$approved_documents = $conn->query("SELECT COUNT(*) as count FROM documents WHERE faculty_id = $faculty_id AND status = 'approved'")->fetch_assoc()['count'];
$pending_documents = $conn->query("SELECT COUNT(*) as count FROM documents WHERE faculty_id = $faculty_id AND status = 'pending'")->fetch_assoc()['count'];

// Calculate approval rates
$research_approval_rate = $research_count > 0 ? round(($approved_research / $research_count) * 100, 1) : 0;
$leave_approval_rate = $leave_requests > 0 ? round(($approved_leaves / $leave_requests) * 100, 1) : 0;
$document_approval_rate = $documents_count > 0 ? round(($approved_documents / $documents_count) * 100, 1) : 0;

// Get monthly trends for research (last 6 months) using submission_date
$research_trend_query = "
    SELECT 
        DATE_FORMAT(submission_date, '%b') as month,
        MONTH(submission_date) as month_num,
        COUNT(*) as count
    FROM research
    WHERE faculty_id = $faculty_id AND submission_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY MONTH(submission_date), DATE_FORMAT(submission_date, '%b')
    ORDER BY MONTH(submission_date)
";
$research_trends = $conn->query($research_trend_query);
$research_trend_data = [];
while ($row = $research_trends->fetch_assoc()) {
    $research_trend_data[] = $row;
}

// Recent activity (last 30 days)
$recent_research = $conn->query("SELECT COUNT(*) as count FROM research WHERE faculty_id = $faculty_id AND submission_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
$recent_leaves = $conn->query("SELECT COUNT(*) as count FROM leaves WHERE faculty_id = $faculty_id AND requested_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
$recent_documents = $conn->query("SELECT COUNT(*) as count FROM documents WHERE faculty_id = $faculty_id AND uploaded_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
?>

<style>
.faculty-dashboard {
    padding: var(--spacing-xl);
}

.dashboard-header {
    margin-bottom: var(--spacing-xl);
}

.welcome-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.welcome-subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.kpi-card {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-dark) 100%);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: radial-gradient(circle, rgba(0, 212, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(30%, -30%);
}

.kpi-card:hover {
    border-color: var(--accent);
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 212, 255, 0.2);
}

.kpi-icon {
    font-size: 2rem;
    margin-bottom: var(--spacing-md);
}

.kpi-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: var(--spacing-sm);
}

.kpi-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: var(--spacing-sm);
}

.kpi-metrics {
    display: flex;
    gap: var(--spacing-lg);
    margin-top: var(--spacing-md);
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--border-color);
}

.metric-item {
    flex: 1;
}

.metric-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.metric-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
}

.status-bar {
    display: flex;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-md);
}

.status-item {
    flex: 1;
    text-align: center;
    padding: var(--spacing-sm);
    border-radius: var(--radius-sm);
    background-color: var(--primary);
}

.status-item.approved {
    background-color: rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.status-item.pending {
    background-color: rgba(245, 158, 11, 0.2);
    color: var(--warning);
}

.status-item.rejected {
    background-color: rgba(239, 68, 68, 0.2);
    color: var(--danger);
}

.chart-section {
    background-color: var(--primary-light);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
}

.chart-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}

.chart-canvas {
    height: 300px;
    position: relative;
}

.quick-actions-section {
    background-color: var(--primary-light);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--spacing-lg);
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-xl);
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    color: var(--text-primary);
}

.action-card:hover {
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
    color: var(--bg-dark);
    border-color: var(--accent);
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 212, 255, 0.3);
}

.action-icon {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-md);
}

.action-text {
    font-weight: 600;
    text-align: center;
    font-size: 1rem;
}

.activity-section {
    background-color: var(--primary-light);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

.activity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
}

.activity-card {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    text-align: center;
}

.activity-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: var(--spacing-sm);
}

.activity-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .kpi-value {
        font-size: 2rem;
    }
    
    .kpi-metrics {
        flex-direction: column;
        gap: var(--spacing-md);
    }
}
</style>

<div class="faculty-dashboard">
    <div class="dashboard-header">
        <h1 class="welcome-title">Welcome, <?php echo htmlspecialchars($current_user['name'] ?? 'User'); ?>!</h1>
        <p class="welcome-subtitle">Here's your activity overview and performance metrics</p>
    </div>

    <!-- Main KPI Cards -->
    <div class="kpi-grid">
        <!-- Research Card -->
        <div class="kpi-card">
            <div class="kpi-icon">🔬</div>
            <div class="kpi-label">Research Submissions</div>
            <div class="kpi-value"><?php echo $research_count; ?></div>
            
            <div class="status-bar">
                <div class="status-item approved">
                    <div style="font-weight: 600;"><?php echo $approved_research; ?></div>
                    <div style="font-size: 0.7rem;">Approved</div>
                </div>
                <div class="status-item pending">
                    <div style="font-weight: 600;"><?php echo $pending_research; ?></div>
                    <div style="font-size: 0.7rem;">Pending</div>
                </div>
                <div class="status-item rejected">
                    <div style="font-weight: 600;"><?php echo $rejected_research; ?></div>
                    <div style="font-size: 0.7rem;">Declined</div>
                </div>
            </div>
            
            <div style="margin-top: var(--spacing-md); text-align: center;">
                <span style="color: var(--accent); font-weight: 600; font-size: 1.2rem;"><?php echo $research_approval_rate; ?>%</span>
                <span style="color: var(--text-secondary); font-size: 0.85rem;"> approval rate</span>
            </div>
        </div>

        <!-- Leave Requests Card -->
        <div class="kpi-card">
            <div class="kpi-icon">📅</div>
            <div class="kpi-label">Leave Requests</div>
            <div class="kpi-value"><?php echo $leave_requests; ?></div>
            
            <div class="kpi-metrics">
                <div class="metric-item">
                    <div class="metric-value" style="color: var(--success);"><?php echo $approved_leaves; ?></div>
                    <div class="metric-label">Approved</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value" style="color: var(--warning);"><?php echo $pending_leaves; ?></div>
                    <div class="metric-label">Pending</div>
                </div>
            </div>
            
            <div style="margin-top: var(--spacing-md); text-align: center;">
                <span style="color: var(--accent); font-weight: 600; font-size: 1.2rem;"><?php echo $leave_approval_rate; ?>%</span>
                <span style="color: var(--text-secondary); font-size: 0.85rem;"> approval rate</span>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="kpi-card">
            <div class="kpi-icon">📚</div>
            <div class="kpi-label">Assigned Courses</div>
            <div class="kpi-value"><?php echo $courses_count; ?></div>
            
            <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-md); border-top: 1px solid var(--border-color);">
                <div style="color: var(--text-secondary); font-size: 0.85rem;">
                    Active teaching assignments
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <div class="kpi-card">
            <div class="kpi-icon">📄</div>
            <div class="kpi-label">Documents</div>
            <div class="kpi-value"><?php echo $documents_count; ?></div>
            
            <div class="kpi-metrics">
                <div class="metric-item">
                    <div class="metric-value" style="color: var(--success);"><?php echo $approved_documents; ?></div>
                    <div class="metric-label">Approved</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value" style="color: var(--warning);"><?php echo $pending_documents; ?></div>
                    <div class="metric-label">Pending</div>
                </div>
            </div>
            
            <div style="margin-top: var(--spacing-md); text-align: center;">
                <span style="color: var(--accent); font-weight: 600; font-size: 1.2rem;"><?php echo $document_approval_rate; ?>%</span>
                <span style="color: var(--text-secondary); font-size: 0.85rem;"> approval rate</span>
            </div>
        </div>
    </div>

    <!-- Research Trends Chart -->
    <div class="chart-section">
        <div class="chart-header">
            <h2 class="chart-title">📊 Research Activity Trends</h2>
        </div>
        <div class="chart-canvas">
            <canvas id="researchTrendsChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-section">
        <h2 class="section-title">🕒 Recent Activity (Last 30 Days)</h2>
        <div class="activity-grid">
            <div class="activity-card">
                <div class="activity-value"><?php echo $recent_research; ?></div>
                <div class="activity-label">Research Submitted</div>
            </div>
            <div class="activity-card">
                <div class="activity-value"><?php echo $recent_leaves; ?></div>
                <div class="activity-label">Leave Requests</div>
            </div>
            <div class="activity-card">
                <div class="activity-value"><?php echo $recent_documents; ?></div>
                <div class="activity-label">Documents Uploaded</div>
            </div>
            <div class="activity-card">
                <div class="activity-value"><?php echo $recent_research + $recent_leaves + $recent_documents; ?></div>
                <div class="activity-label">Total Activities</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <h2 class="section-title">⚡ Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="research.php" class="action-card">
                <div class="action-icon">🔬</div>
                <div class="action-text">Submit Research</div>
            </a>
            <a href="leave.php" class="action-card">
                <div class="action-icon">📅</div>
                <div class="action-text">Request Leave</div>
            </a>
            <a href="documents.php" class="action-card">
                <div class="action-icon">📄</div>
                <div class="action-text">Upload Document</div>
            </a>
            <a href="timetable.php" class="action-card">
                <div class="action-icon">⏰</div>
                <div class="action-text">View Timetable</div>
            </a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Research trend data from PHP
const researchTrendData = <?php echo json_encode($research_trend_data); ?>;

// Prepare chart data (last 6 months)
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const currentMonth = new Date().getMonth();
const last6Months = [];
const researchData = [];

for (let i = 5; i >= 0; i--) {
    const monthIndex = (currentMonth - i + 12) % 12;
    last6Months.push(months[monthIndex]);
    researchData.push(0);
}

researchTrendData.forEach(item => {
    const index = last6Months.indexOf(item.month);
    if (index !== -1) {
        researchData[index] = parseInt(item.count);
    }
});

// Create chart
const ctx = document.getElementById('researchTrendsChart').getContext('2d');
const researchChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: last6Months,
        datasets: [{
            label: 'Research Submissions',
            data: researchData,
            backgroundColor: 'rgba(0, 212, 255, 0.1)',
            borderColor: 'rgba(0, 212, 255, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(0, 212, 255, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    color: '#e0e0e0',
                    font: { size: 12 },
                    padding: 20
                }
            },
            tooltip: {
                backgroundColor: '#1a1a2e',
                titleColor: '#00d4ff',
                bodyColor: '#e0e0e0',
                borderColor: '#2a2a3e',
                borderWidth: 1,
                padding: 12,
                displayColors: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(42, 42, 62, 0.5)',
                    drawBorder: false
                },
                ticks: {
                    color: '#a0a0a0',
                    font: { size: 11 },
                    stepSize: 1
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#a0a0a0',
                    font: { size: 11 }
                }
            }
        }
    }
});
</script>

<?php require_once 'footer.php'; ?>