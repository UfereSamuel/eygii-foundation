<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Programs Management";
$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_program') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        if (!empty($title) && !empty($description)) {
            try {
                // Handle image upload
                $image_path = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../../assets/images/programs/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $filename = 'program_' . time() . '_' . uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_path = 'assets/images/programs/' . $filename;
                        }
                    }
                }
                
                $stmt = $db->getConnection()->prepare("INSERT INTO programs (title, description, content, image_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $content, $image_path, $status, date('Y-m-d H:i:s')]);
                $success_message = "Program added successfully.";
            } catch (Exception $e) {
                $error_message = "Failed to add program: " . $e->getMessage();
            }
        } else {
            $error_message = "Title and description are required.";
        }
    }
    
    if ($action === 'edit_program') {
        $program_id = $_POST['program_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        if (!empty($program_id) && !empty($title) && !empty($description)) {
            try {
                // Get current program data
                $current_program = $db->fetch("SELECT * FROM programs WHERE id = ?", [$program_id]);
                
                if ($current_program) {
                    $image_path = $current_program['image_path'];
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../../assets/images/programs/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        
                        if (in_array($file_extension, $allowed_extensions)) {
                            $filename = 'program_' . time() . '_' . uniqid() . '.' . $file_extension;
                            $target_path = $upload_dir . $filename;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                                // Delete old image if exists
                                if ($image_path && file_exists('../../' . $image_path)) {
                                    unlink('../../' . $image_path);
                                }
                                $image_path = 'assets/images/programs/' . $filename;
                            }
                        }
                    }
                    
                    $stmt = $db->getConnection()->prepare("UPDATE programs SET title = ?, description = ?, content = ?, image_path = ?, status = ?, updated_at = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $content, $image_path, $status, date('Y-m-d H:i:s'), $program_id]);
                    $success_message = "Program updated successfully.";
                } else {
                    $error_message = "Program not found.";
                }
            } catch (Exception $e) {
                $error_message = "Failed to update program: " . $e->getMessage();
            }
        } else {
            $error_message = "All required fields must be filled.";
        }
    }
    
    if ($action === 'delete_program') {
        $program_id = $_POST['program_id'] ?? '';
        
        try {
            // Get program data to delete image
            $program = $db->fetch("SELECT image_path FROM programs WHERE id = ?", [$program_id]);
            
            if ($program) {
                // Delete image file if exists
                if ($program['image_path'] && file_exists('../../' . $program['image_path'])) {
                    unlink('../../' . $program['image_path']);
                }
                
                $stmt = $db->getConnection()->prepare("DELETE FROM programs WHERE id = ?");
                $stmt->execute([$program_id]);
                $success_message = "Program deleted successfully.";
            } else {
                $error_message = "Program not found.";
            }
        } catch (Exception $e) {
            $error_message = "Failed to delete program: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_status') {
        $program_id = $_POST['program_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        
        try {
            $stmt = $db->getConnection()->prepare("UPDATE programs SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, date('Y-m-d H:i:s'), $program_id]);
            $success_message = "Program status updated successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to update status: " . $e->getMessage();
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ? OR content LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get programs with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM programs {$where_clause}";
    $stmt = $db->getConnection()->prepare($count_sql);
    $stmt->execute($params);
    $total_programs = $stmt->fetch()['total'];
    $total_pages = ceil($total_programs / $per_page);
    
    // Get programs
    $sql = "SELECT * FROM programs {$where_clause} ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute($params);
    $programs = $stmt->fetchAll();
    
    // Get status counts
    $status_counts = $db->fetchAll("SELECT status, COUNT(*) as count FROM programs GROUP BY status");
    $counts = [];
    foreach ($status_counts as $row) {
        $counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    $error_message = "Failed to fetch programs: " . $e->getMessage();
    $programs = [];
    $total_programs = 0;
    $total_pages = 0;
    $counts = [];
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-project-diagram me-2"></i>Programs Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                            <i class="fas fa-plus me-1"></i>Add Program
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-project-diagram fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($total_programs); ?></h5>
                            <p class="card-text text-muted">Total Programs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['active'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Active</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-pause-circle fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['draft'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Draft</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-archive fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['archived'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Archived</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Filter by Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by title, description, or content...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Programs Grid -->
            <div class="row">
                <?php if (empty($programs)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No programs found</h5>
                            <p class="text-muted">Create your first program to get started.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                                <i class="fas fa-plus me-1"></i>Add Program
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($programs as $program): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <?php if ($program['image_path']): ?>
                                    <img src="../../<?php echo htmlspecialchars($program['image_path']); ?>" 
                                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                                         alt="<?php echo htmlspecialchars($program['title']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title"><?php echo htmlspecialchars($program['title']); ?></h5>
                                        <span class="badge bg-<?php 
                                            echo match($program['status']) {
                                                'active' => 'success',
                                                'draft' => 'warning',
                                                'archived' => 'secondary',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($program['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="card-text text-muted flex-grow-1">
                                        <?php echo htmlspecialchars(substr($program['description'], 0, 120)); ?>
                                        <?php echo strlen($program['description']) > 120 ? '...' : ''; ?>
                                    </p>
                                    
                                    <div class="mt-auto">
                                        <small class="text-muted d-block mb-2">
                                            Created: <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                        </small>
                                        
                                        <div class="btn-group w-100">
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    onclick="editProgram(<?php echo $program['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    onclick="viewProgram(<?php echo $program['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="deleteProgram(<?php echo $program['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Programs pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_program">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Program Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Short Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Full Content</label>
                        <textarea class="form-control" id="content" name="content" rows="6"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Program Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WebP</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Program Modal -->
<div class="modal fade" id="editProgramModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body" id="editProgramModalBody">
                    <!-- Content loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Program Modal -->
<div class="modal fade" id="viewProgramModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Program Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewProgramModalBody">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function editProgram(programId) {
    fetch(`../api/get_program.php?id=${programId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const program = data.program;
                document.getElementById('editProgramModalBody').innerHTML = `
                    <input type="hidden" name="action" value="edit_program">
                    <input type="hidden" name="program_id" value="${program.id}">
                    
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Program Title *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" value="${program.title}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Short Description *</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required>${program.description}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Full Content</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="6">${program.content || ''}</textarea>
                    </div>
                    
                    ${program.image_path ? `
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img src="../../${program.image_path}" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">New Image (optional)</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        <small class="form-text text-muted">Leave empty to keep current image</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active" ${program.status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="draft" ${program.status === 'draft' ? 'selected' : ''}>Draft</option>
                            <option value="archived" ${program.status === 'archived' ? 'selected' : ''}>Archived</option>
                        </select>
                    </div>
                `;
                
                new bootstrap.Modal(document.getElementById('editProgramModal')).show();
            } else {
                alert('Failed to load program details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load program details');
        });
}

function viewProgram(programId) {
    fetch(`../api/get_program.php?id=${programId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const program = data.program;
                document.getElementById('viewProgramModalBody').innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <h4>${program.title}</h4>
                            <p class="text-muted">${program.description}</p>
                            ${program.content ? `<div class="mt-3">${program.content.replace(/\n/g, '<br>')}</div>` : ''}
                        </div>
                        <div class="col-md-4">
                            ${program.image_path ? `
                                <img src="../../${program.image_path}" class="img-fluid rounded mb-3" alt="${program.title}">
                            ` : ''}
                            <p><strong>Status:</strong> <span class="badge bg-${program.status === 'active' ? 'success' : program.status === 'draft' ? 'warning' : 'secondary'}">${program.status.charAt(0).toUpperCase() + program.status.slice(1)}</span></p>
                            <p><strong>Created:</strong> ${new Date(program.created_at).toLocaleDateString()}</p>
                            ${program.updated_at ? `<p><strong>Updated:</strong> ${new Date(program.updated_at).toLocaleDateString()}</p>` : ''}
                        </div>
                    </div>
                `;
                
                new bootstrap.Modal(document.getElementById('viewProgramModal')).show();
            } else {
                alert('Failed to load program details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load program details');
        });
}

function deleteProgram(programId) {
    if (confirm('Are you sure you want to delete this program? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_program');
        formData.append('program_id', programId);
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete program');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?> 