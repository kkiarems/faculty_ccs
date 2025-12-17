<?php
$page_title = 'Document Versions';
require_once 'header.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user = $_SESSION['current_user'] ?? null;
$faculty_id = isset($current_user['faculty_id']) ? (int)$current_user['faculty_id'] : 0;

if ($faculty_id <= 0) {
    echo '<div class="alert alert-danger">Session expired. Please log in again.</div>';
    require_once 'footer.php';
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: documents.php");
    exit;
}

$document_id = intval($_GET['id']);

// Get the main document (could be parent or child)
$main_query = "SELECT * FROM documents WHERE (document_id = ? OR parent_document_id = ?) AND faculty_id = ? LIMIT 1";
$stmt = $conn->prepare($main_query);
$stmt->bind_param("iii", $document_id, $document_id, $faculty_id);
$stmt->execute();
$main_result = $stmt->get_result();
$main_doc = $main_result->fetch_assoc();

if (!$main_doc) {
    header("Location: documents.php");
    exit;
}

$parent_id = $main_doc['parent_document_id'] ?? $main_doc['document_id'];

// Get all versions
$versions_query = "SELECT * FROM documents WHERE (document_id = ? OR parent_document_id = ?) 
                  AND faculty_id = ? ORDER BY version_number DESC";
$stmt = $conn->prepare($versions_query);
$stmt->bind_param("iii", $parent_id, $parent_id, $faculty_id);
$stmt->execute();
$versions_result = $stmt->get_result();
?>

<style>
    .version-header {
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }
    .version-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: var(--spacing-md);
    }
    .version-timeline {
        position: relative;
        padding: var(--spacing-lg) 0;
    }
    .version-item {
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        position: relative;
        padding-left: var(--spacing-xl);
    }
    .version-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #3b82f6;
        border-radius: var(--radius-md) 0 0 var(--radius-md);
    }
    .version-item.latest::before {
        background-color: #10b981;
    }
    .version-number {
        display: inline-block;
        background-color: #3b82f6;
        color: white;
        padding: var(--spacing-xs) var(--spacing-md);
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: var(--spacing-md);
    }
    .version-item.latest .version-number {
        background-color: #10b981;
    }
    .version-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-md);
        font-size: 0.9rem;
        color: var(--text-secondary);
    }
    .version-meta-item {
        display: flex;
        flex-direction: column;
    }
    .version-meta-label {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
    }
    .status-badge {
        display: inline-block;
        padding: var(--spacing-xs) var(--spacing-md);
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        font-weight: 500;
        width: fit-content;
    }
    .status-pending { background-color: rgba(245,158,11,0.1); color: var(--warning); }
    .status-approved { background-color: rgba(16,185,129,0.1); color: var(--success); }
    .status-rejected { background-color: rgba(239,68,68,0.1); color: var(--danger); }
    .version-actions {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-md);
    }
    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: 0.85rem;
    }
    .back-link {
        display: inline-block;
        margin-bottom: var(--spacing-lg);
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
    }
    .back-link:hover {
        text-decoration: underline;
    }
</style>

<a href="documents.php" class="back-link">← Back to Documents</a>

<div class="version-header">
    <div class="version-title"><?php echo htmlspecialchars($main_doc['document_name']); ?></div>
    <p style="color: var(--text-secondary); margin: 0;">
        Total Versions: <strong><?php echo $versions_result->num_rows; ?></strong>
    </p>
</div>

<div class="version-timeline">
    <?php 
    $version_count = 0;
    while ($version = $versions_result->fetch_assoc()): 
        $version_count++;
        $is_latest = $version['is_latest'];
    ?>
    <div class="version-item <?php echo $is_latest ? 'latest' : ''; ?>">
        <div class="version-number">
            v<?php echo $version['version_number']; ?>
            <?php if ($is_latest): ?>
            <span style="margin-left: var(--spacing-sm);">(Current)</span>
            <?php endif; ?>
        </div>
        
        <div class="version-meta">
            <div class="version-meta-item">
                <span class="version-meta-label">Uploaded</span>
                <span><?php echo date('M d, Y H:i', strtotime($version['uploaded_date'])); ?></span>
            </div>
            
            <div class="version-meta-item">
                <span class="version-meta-label">File Size</span>
                <span><?php echo $version['file_size'] ? round($version['file_size'] / 1024 / 1024, 2) . 'MB' : 'N/A'; ?></span>
            </div>
            
            <div class="version-meta-item">
                <span class="version-meta-label">Status</span>
                <span class="status-badge status-<?php echo htmlspecialchars($version['status']); ?>">
                    <?php echo ucfirst($version['status']); ?>
                </span>
            </div>
            
            <?php if ($version['approval_date']): ?>
            <div class="version-meta-item">
                <span class="version-meta-label">Approved</span>
                <span><?php echo date('M d, Y', strtotime($version['approval_date'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="version-actions">
            <?php if ($version['file_path'] && file_exists($version['file_path'])): ?>
            <a href="download-document.php?id=<?php echo $version['document_id']; ?>" class="btn btn-secondary btn-sm">Download</a>
            <?php endif; ?>
            
            <?php if (!$is_latest && $version['status'] === 'approved'): ?>
            <button class="btn btn-info btn-sm" onclick="compareVersions(<?php echo $version['document_id']; ?>)">Compare</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<script>
function compareVersions(versionId) {
    alert('Version comparison feature coming soon!');
}
</script>

<?php require_once 'footer.php'; ?>
