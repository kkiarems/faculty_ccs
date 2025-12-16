<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/notifications.php';

$user_type = 'admin';
$current_user = getCurrentUser(conn: $conn);
requireAdmin();

$user_id = $current_user['admin_id'];

$unread_count = getUnreadCount($conn, $user_type, $user_id);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - FMS Admin' : 'Faculty Management System - Admin'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            border-right: 1px solid var(--border-color);
            padding: var(--spacing-lg);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .sidebar::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
        .sidebar-header {
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: var(--spacing-sm);
        }
        .sidebar-subtitle {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: var(--spacing-sm);
        }
        .sidebar-menu a {
            display: block;
            padding: var(--spacing-md) var(--spacing-md);
            color: var(--text-secondary);
            border-radius: var(--radius-md);
            transition: var(--transition);
            text-decoration: none;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--primary-light);
            color: var(--accent);
        }
        .main-content {
            margin-left: 250px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background-color: var(--primary-light);
            border-bottom: 1px solid var(--border-color);
            padding: var(--spacing-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .topbar-user {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }
        .user-info {
            text-align: right;
        }
        .user-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        .user-role {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg-dark);
            font-weight: 700;
        }
        .content {
            flex: 1;
            padding: var(--spacing-xl);
            overflow-y: auto;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            .main-content {
                margin-left: 0;
            }
            .admin-layout {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">FMS</div>
                <div class="sidebar-subtitle">Admin Panel</div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
                <li><a href="faculty-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'faculty-management.php' ? 'active' : ''; ?>">👥 Faculty Management</a></li>
                <li><a href="course-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'course-management.php' ? 'active' : ''; ?>">📚 Courses</a></li>
                <li><a href="research-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'research-management.php' ? 'active' : ''; ?>">🔬 Research</a></li>
                <li><a href="leave-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'leave-management.php' ? 'active' : ''; ?>">📅 Leave Requests</a></li>
                <li><a href="documents-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'documents-management.php' ? 'active' : ''; ?>">📄 Documents</a></li>
                <li><a href="timetable-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'timetable-management.php' ? 'active' : ''; ?>">⏰ Timetable</a></li>
                <li><a href="admin-management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'admin-management.php' ? 'active' : ''; ?>">🔐 Admin Users</a></li>
                <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">⚙️ Profile</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></div>
                <div class="topbar-user">

    <div class="notification-bell" id="notificationBell">
        <span class="bell-icon">🔔</span>
        <?php if ($unread_count > 0): ?>
            <span class="notification-badge"><?php echo $unread_count > 99 ? '99+' : $unread_count; ?></span>
        <?php endif; ?>
        
        <div class="notification-dropdown" id="notificationDropdown">
        </div>
    </div>
    
    <div class="user-info">
        <div class="user-name"><?php echo isset($current_user['name']) ? htmlspecialchars($current_user['name']) : 'Unknown User'; ?></div>
        <div class="user-role">Administrator</div>
    </div>
    <div class="user-avatar"><?php echo isset($current_user['name']) ? strtoupper(substr($current_user['name'], 0, 1)) : '?'; ?></div>
</div>
            </div>
            <div class="content">


</body>
</html>
