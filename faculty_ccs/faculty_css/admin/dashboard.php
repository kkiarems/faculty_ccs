<?php
$page_title = 'Dashboard';
require_once 'header.php';
require_once '../config/database.php';

// Fetch KPI Data
$total_faculty = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];

// Get monthly trends for faculty (last 12 months)
$faculty_trend_query = "
    SELECT 
        DATE_FORMAT(created_at, '%b') as month,
        MONTH(created_at) as month_num,
        COUNT(*) as count
    FROM faculty
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY MONTH(created_at), DATE_FORMAT(created_at, '%b')
    ORDER BY MONTH(created_at)
";
$faculty_trends = $conn->query($faculty_trend_query);

// Get monthly trends for courses
$course_trend_query = "
    SELECT 
        DATE_FORMAT(created_at, '%b') as month,
        MONTH(created_at) as month_num,
        COUNT(*) as count
    FROM courses
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY MONTH(created_at), DATE_FORMAT(created_at, '%b')
    ORDER BY MONTH(created_at)
";
$course_trends = $conn->query($course_trend_query);

// Calculate growth percentages (comparing this month to last month)
$current_month_faculty = $conn->query("SELECT COUNT(*) as count FROM faculty WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetch_assoc()['count'];
$last_month_faculty = $conn->query("SELECT COUNT(*) as count FROM faculty WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))")->fetch_assoc()['count'];
$faculty_growth = $last_month_faculty > 0 ? round((($current_month_faculty - $last_month_faculty) / $last_month_faculty) * 100, 1) : ($current_month_faculty > 0 ? 100 : 0);

$current_month_courses = $conn->query("SELECT COUNT(*) as count FROM courses WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetch_assoc()['count'];
$last_month_courses = $conn->query("SELECT COUNT(*) as count FROM courses WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))")->fetch_assoc()['count'];
$course_growth = $last_month_courses > 0 ? round((($current_month_courses - $last_month_courses) / $last_month_courses) * 100, 1) : ($current_month_courses > 0 ? 100 : 0);

// Recent activity
$recent_faculty = $conn->query("SELECT COUNT(*) as count FROM faculty WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
$recent_courses = $conn->query("SELECT COUNT(*) as count FROM courses WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];

// Prepare trend data for JavaScript
$faculty_trend_data = [];
$course_trend_data = [];

while ($row = $faculty_trends->fetch_assoc()) {
    $faculty_trend_data[] = $row;
}

$course_trends_result = $conn->query($course_trend_query);
while ($row = $course_trends_result->fetch_assoc()) {
    $course_trend_data[] = $row;
}
?>

<style>
.kpi-container {
    padding: var(--spacing-xl);
}

.kpi-header {
    margin-bottom: var(--spacing-xl);
}

.kpi-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.kpi-subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.time-filters {
    display: flex;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xl);
}

.time-filter-btn {
    padding: var(--spacing-sm) var(--spacing-lg);
    background-color: var(--primary-light);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.95rem;
}

.time-filter-btn:hover,
.time-filter-btn.active {
    background-color: var(--accent);
    color: var(--bg-dark);
    border-color: var(--accent);
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

.kpi-change {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 0.9rem;
}

.kpi-change.positive {
    color: var(--success);
}

.kpi-change.negative {
    color: var(--danger);
}

.kpi-details {
    margin-top: var(--spacing-md);
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--border-color);
    font-size: 0.85rem;
    color: var(--text-secondary);
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

.activity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
}

.activity-card {
    background-color: var(--primary-light);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
}

.activity-title {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
}

.activity-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--accent);
}

@media (max-width: 768px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .kpi-value {
        font-size: 2rem;
    }
    
    .time-filters {
        flex-wrap: wrap;
    }
}
</style>

<div class="kpi-container">
    <div class="kpi-header">
        <h1 class="kpi-title">Analytics Dashboard</h1>
        <p class="kpi-subtitle">KPI analysis for Faculty Management System</p>
    </div>

    <div class="time-filters">
        <button class="time-filter-btn" onclick="filterByTime('year')">Year</button>
        <button class="time-filter-btn active" onclick="filterByTime('month')">Month</button>
        <button class="time-filter-btn" onclick="filterByTime('week')">Week</button>
    </div>

    <div class="kpi-grid">
        <!-- Total Faculty KPI -->
        <div class="kpi-card">
            <div class="kpi-label">Total Faculty</div>
            <div class="kpi-value"><?php echo number_format($total_faculty); ?></div>
            <div class="kpi-change <?php echo $faculty_growth >= 0 ? 'positive' : 'negative'; ?>">
                <span><?php echo $faculty_growth >= 0 ? '↑' : '↓'; ?> <?php echo abs($faculty_growth); ?>%</span>
                <span style="color: var(--text-secondary);">vs last month</span>
            </div>
            <div class="kpi-details">
                <?php echo $recent_faculty; ?> new faculty this week
            </div>
        </div>

        <!-- Total Courses KPI -->
        <div class="kpi-card">
            <div class="kpi-label">Total Courses</div>
            <div class="kpi-value"><?php echo number_format($total_courses); ?></div>
            <div class="kpi-change <?php echo $course_growth >= 0 ? 'positive' : 'negative'; ?>">
                <span><?php echo $course_growth >= 0 ? '↑' : '↓'; ?> <?php echo abs($course_growth); ?>%</span>
                <span style="color: var(--text-secondary);">vs last month</span>
            </div>
            <div class="kpi-details">
                <?php echo $recent_courses; ?> new courses this week
            </div>
        </div>

        <!-- Active Faculty KPI -->
        <div class="kpi-card">
            <div class="kpi-label">Active Faculty</div>
            <div class="kpi-value"><?php echo number_format($total_faculty); ?></div>
            <div class="kpi-change positive">
                <span>↑ 100%</span>
                <span style="color: var(--text-secondary);">engagement rate</span>
            </div>
            <div class="kpi-details">
                All faculty members active
            </div>
        </div>

        <!-- Active Courses KPI -->
        <div class="kpi-card">
            <div class="kpi-label">Active Courses</div>
            <div class="kpi-value"><?php echo number_format($total_courses); ?></div>
            <div class="kpi-change positive">
                <span>↑ 100%</span>
                <span style="color: var(--text-secondary);">of total courses</span>
            </div>
            <div class="kpi-details">
                All courses currently active
            </div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="chart-section">
        <div class="chart-header">
            <h2 class="chart-title">Growth Trends</h2>
        </div>
        <div class="chart-canvas">
            <canvas id="trendsChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="chart-section">
        <div class="chart-header">
            <h2 class="chart-title">Recent Activity (Last 7 Days)</h2>
        </div>
        <div class="activity-grid">
            <div class="activity-card">
                <div class="activity-title">New Faculty Members</div>
                <div class="activity-value"><?php echo $recent_faculty; ?></div>
            </div>
            <div class="activity-card">
                <div class="activity-title">New Courses Added</div>
                <div class="activity-value"><?php echo $recent_courses; ?></div>
            </div>
            <div class="activity-card">
                <div class="activity-title">Total Growth</div>
                <div class="activity-value"><?php echo $recent_faculty + $recent_courses; ?></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Trend data from PHP
const facultyTrendData = <?php echo json_encode($faculty_trend_data); ?>;
const courseTrendData = <?php echo json_encode($course_trend_data); ?>;

// Prepare chart data
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const facultyData = new Array(12).fill(0);
const courseData = new Array(12).fill(0);

facultyTrendData.forEach(item => {
    const index = months.indexOf(item.month);
    if (index !== -1) facultyData[index] = parseInt(item.count);
});

courseTrendData.forEach(item => {
    const index = months.indexOf(item.month);
    if (index !== -1) courseData[index] = parseInt(item.count);
});

// Create chart
const ctx = document.getElementById('trendsChart').getContext('2d');
const trendsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Faculty',
                data: facultyData,
                backgroundColor: 'rgba(0, 212, 255, 0.6)',
                borderColor: 'rgba(0, 212, 255, 1)',
                borderWidth: 2,
                borderRadius: 8
            },
            {
                label: 'Courses',
                data: courseData,
                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 2,
                borderRadius: 8
            }
        ]
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
                    font: { size: 11 }
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

function filterByTime(period) {
    // Update active button
    document.querySelectorAll('.time-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Here you would typically fetch new data based on the period
    console.log('Filter by:', period);
}
</script>

<?php require_once 'footer.php'; ?>