<?php
$page_title = 'Documents';
require_once 'header.php';
require_once '../config/database.php';
require_once '../config/notifications.php';

// Get current logged-in faculty safely
$faculty_id = $_SESSION['faculty_id'] ?? 0;

$message = '';

// Prevent running queries without valid faculty_id
if ($faculty_id <= 0) {
    echo '<div class="alert alert-danger">Session expired. Please log in again.</div>';
    require_once 'footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    $document_name = sanitize($_POST['document_name']);
    $document_type = sanitize($_POST['document_type']);
    $category = sanitize($_POST['category']);
    
    // Validate file upload
    if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-danger">Error uploading file. Please try again.</div>';
    } else {
        $file = $_FILES['document_file'];
        $file_size = $file['size'];
        $original_filename = $file['name'];
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
        
        if (!in_array($file_extension, $allowed_types)) {
            $message = '<div class="alert alert-danger">File type not allowed. Allowed types: ' . implode(', ', $allowed_types) . '</div>';
        } elseif ($file_size > 52428800) { // 50MB limit
            $message = '<div class="alert alert-danger">File size exceeds 50MB limit.</div>';
        } else {
            // Create uploads directory if it doesn't exist
            $uploads_dir = '../uploads/documents/' . $faculty_id;
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0755, true);
            }
            
            // Generate unique filename
            $unique_filename = uniqid('doc_') . '_' . time() . '.' . $file_extension;
            $file_path = 'uploads/documents/' . $faculty_id . '/' . $unique_filename;
            $full_path = '../' . $file_path;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $full_path)) {
                $insert = "INSERT INTO documents (faculty_id, document_name, document_type, category, 
                           file_path, file_size, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($insert);
                $stmt->bind_param("issssi", $faculty_id, $document_name, $document_type, $category, 
                                 $file_path, $file_size);
                
                if ($stmt->execute()) {
                    $document_id = $conn->insert_id;
                    
                    // Get faculty name
                    $faculty = $conn->query("SELECT name FROM faculty WHERE faculty_id = $faculty_id")->fetch_assoc();
                    $faculty_name = $faculty['name'];
                    
                    // Notify all admins
                    notifyDocumentUploaded($conn, $faculty_name, $document_name, $document_id);
                    
                    $message = '<div class="alert alert-success">✓ Document uploaded successfully!</div>';
                } else {
                    unlink($full_path);
                    $message = '<div class="alert alert-danger">Error saving document to database</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Error uploading file</div>';
            }
        }
    }
}

// Handle delete document
if (isset($_GET['delete'])) {
    $document_id = intval($_GET['delete']);
    
    // Get file path before deleting
    $stmt = $conn->prepare("SELECT file_path FROM documents WHERE document_id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $document_id, $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $doc = $result->fetch_assoc();
        $file_path = '../' . $doc['file_path'];
        
        // Delete from database
        $delete = "DELETE FROM documents WHERE document_id = ? AND faculty_id = ?";
        $stmt = $conn->prepare($delete);
        $stmt->bind_param("ii", $document_id, $faculty_id);
        
        if ($stmt->execute()) {
            // Delete physical file
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $message = '<div class="alert alert-success">Document deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting document</div>';
        }
    }
}

// Get faculty's documents
$stmt = $conn->prepare("SELECT * FROM documents WHERE faculty_id = ? ORDER BY uploaded_date DESC");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$documents_list = $stmt->get_result();
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
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .form-group {
        margin-bottom: var(--spacing-lg);
    }
    
    .form-group label {
        display: block;
        margin-bottom: var(--spacing-sm);
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .form-group input[type="text"],
    .form-group input[type="file"],
    .form-group select {
        width: 100%;
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--primary);
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    }
    
    .form-group small {
        display: block;
        margin-top: var(--spacing-xs);
        color: var(--text-secondary);
        font-size: 0.85rem;
    }
    
    .file-input-wrapper {
        position: relative;
    }
    
    .file-input-wrapper input[type="file"] {
        cursor: pointer;
    }
    
    .documents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--spacing-lg);
        margin-top: var(--spacing-xl);
    }
    
    .document-card {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-dark) 100%);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        transition: var(--transition);
    }
    
    .document-card:hover {
        border-color: var(--accent);
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 212, 255, 0.2);
    }
    
    .document-icon {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: var(--spacing-md);
    }
    
    .icon-pdf { background: rgba(239, 68, 68, 0.2); }
    .icon-word { background: rgba(59, 130, 246, 0.2); }
    .icon-excel { background: rgba(16, 185, 129, 0.2); }
    .icon-image { background: rgba(245, 158, 11, 0.2); }
    .icon-other { background: rgba(156, 163, 175, 0.2); }
    
    .document-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .document-meta {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
        margin-bottom: var(--spacing-md);
        font-size: 0.85rem;
        color: var(--text-secondary);
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: var(--spacing-md);
    }
    
    .status-pending { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
    .status-approved { background: rgba(16, 185, 129, 0.2); color: var(--success); }
    .status-rejected { background: rgba(239, 68, 68, 0.2); color: var(--danger); }
    
    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-md);
    }
    
    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: 0.85rem;
        flex: 1;
        text-align: center;
    }
    
    .no-documents {
        text-align: center;
        padding: var(--spacing-2xl);
        color: var(--text-secondary);
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
</style>

<div class="page-header">
    <h2>📄 Document Management</h2>
    <p style="color: var(--text-secondary);">Upload and manage your documents</p>
</div>

<?php echo $message; ?>

<div class="form-container">
    <h3>Upload New Document</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label for="document_name">Document Name *</label>
                <input type="text" id="document_name" name="document_name" placeholder="e.g., Course Syllabus 2024" required>
            </div>
            
            <div class="form-group">
                <label for="document_type">Document Type *</label>
                <select id="document_type" name="document_type" required>
                    <option value="">Select Type</option>
                    <option value="PDF">PDF Document</option>
                    <option value="Word">Word Document (.doc, .docx)</option>
                    <option value="Excel">Excel Spreadsheet (.xls, .xlsx)</option>
                    <option value="PowerPoint">PowerPoint Presentation (.ppt, .pptx)</option>
                    <option value="Image">Image (.jpg, .png, .gif)</option>
                    <option value="Text">Text File (.txt)</option>
                    <option value="Archive">Compressed File (.zip, .rar)</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Teaching Materials">Teaching Materials</option>
                    <option value="Syllabus">Course Syllabus</option>
                    <option value="Lesson Plans">Lesson Plans</option>
                    <option value="Assessments">Assessments & Exams</option>
                    <option value="Certificates">Certificates & Awards</option>
                    <option value="HR Documents">HR Documents</option>
                    <option value="Reports">Reports & Evaluations</option>
                    <option value="Research Papers">Research Papers</option>
                    <option value="Training Materials">Training Materials</option>
                    <option value="Personal">Personal Documents</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="document_file">Upload File *</label>
            <div class="file-input-wrapper">
                <input type="file" id="document_file" name="document_file" required 
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar">
            </div>
            <small>Allowed: PDF, Word, Excel, PowerPoint, Images, Text, Archives • Max size: 50MB</small>
        </div>
        
        <button type="submit" name="upload_document" class="btn btn-primary">📤 Upload Document</button>
    </form>
</div>

<h3 style="margin-bottom: var(--spacing-lg);">My Documents</h3>

<?php if ($documents_list->num_rows === 0): ?>
    <div class="no-documents">
        <p style="font-size: 1.2rem; margin-bottom: var(--spacing-sm);">📂 No documents yet</p>
        <p>Upload your first document using the form above</p>
    </div>
<?php else: ?>
    <div class="documents-grid">
        <?php while ($document = $documents_list->fetch_assoc()): 
            // Determine icon based on document type
            $icon = '📄';
            $icon_class = 'icon-other';
            
            if (stripos($document['document_type'], 'pdf') !== false) {
                $icon = '📕';
                $icon_class = 'icon-pdf';
            } elseif (stripos($document['document_type'], 'word') !== false) {
                $icon = '📘';
                $icon_class = 'icon-word';
            } elseif (stripos($document['document_type'], 'excel') !== false) {
                $icon = '📗';
                $icon_class = 'icon-excel';
            } elseif (stripos($document['document_type'], 'image') !== false) {
                $icon = '🖼️';
                $icon_class = 'icon-image';
            } elseif (stripos($document['document_type'], 'powerpoint') !== false) {
                $icon = '📙';
                $icon_class = 'icon-other';
            }
            
            $file_size_mb = isset($document['file_size']) ? round($document['file_size'] / 1024 / 1024, 2) : 0;
        ?>
        <div class="document-card">
            <div class="document-icon <?php echo $icon_class; ?>">
                <?php echo $icon; ?>
            </div>
            
            <span class="status-badge status-<?php echo htmlspecialchars($document['status']); ?>">
                <?php echo ucfirst($document['status']); ?>
            </span>
            
            <div class="document-title" title="<?php echo htmlspecialchars($document['document_name']); ?>">
                <?php echo htmlspecialchars($document['document_name']); ?>
            </div>
            
            <div class="document-meta">
                <span>📋 Type: <?php echo htmlspecialchars($document['document_type']); ?></span>
                <span>🏷️ Category: <?php echo htmlspecialchars($document['category']); ?></span>
                <span>📅 Uploaded: <?php echo date('M d, Y', strtotime($document['uploaded_date'])); ?></span>
                <span>💾 Size: <?php echo $file_size_mb; ?> MB</span>
            </div>
            
            <div class="action-buttons">
                <?php 
                $file_path = '../' . $document['file_path'];
                if (file_exists($file_path)): 
                ?>
                <a href="view-document.php?id=<?php echo $document['document_id']; ?>" 
                   class="btn btn-primary btn-sm" target="_blank">👁️ View</a>
                <?php endif; ?>
                
                <?php if ($document['status'] === 'pending'): ?>
                <a href="?delete=<?php echo $document['document_id']; ?>" 
                   class="btn btn-danger btn-sm" 
                   onclick="return confirm('Are you sure you want to delete this document?');">🗑️ Delete</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>