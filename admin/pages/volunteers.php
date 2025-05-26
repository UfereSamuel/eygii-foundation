<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Volunteers Management";
$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $volunteer_id = $_POST['volunteer_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        
        try {
            $stmt = $db->getConnection()->prepare("UPDATE volunteers SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, date('Y-m-d H:i:s'), $volunteer_id]);
            $success_message = "Volunteer status updated successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to update status: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete') {
        $volunteer_id = $_POST['volunteer_id'] ?? '';
        
        try {
            $stmt = $db->getConnection()->prepare("DELETE FROM volunteers WHERE id = ?");
            $stmt->execute([$volunteer_id]);
            $success_message = "Volunteer record deleted successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to delete volunteer: " . $e->getMessage();
        }
    }
    
    if ($action === 'add_note') {
        $volunteer_id = $_POST['volunteer_id'] ?? '';
        $note = trim($_POST['note'] ?? '');
        
        if (!empty($volunteer_id) && !empty($note)) {
            try {
                // Get current notes
                $current_volunteer = $db->fetch("SELECT notes FROM volunteers WHERE id = ?", [$volunteer_id]);
                $existing_notes = $current_volunteer['notes'] ?? '';
                
                $new_note = "[" . date('Y-m-d H:i:s') . "] " . $note;
                $updated_notes = $existing_notes ? $existing_notes . "\n" . $new_note : $new_note;
                
                $stmt = $db->getConnection()->prepare("UPDATE volunteers SET notes = ?, updated_at = ? WHERE id = ?");
                $stmt->execute([$updated_notes, date('Y-m-d H:i:s'), $volunteer_id]);
                $success_message = "Note added successfully.";
            } catch (Exception $e) {
                $error_message = "Failed to add note: " . $e->getMessage();
            }
        } else {
            $error_message = "Note content is required.";
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
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR skills LIKE ? OR interests LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get volunteers with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM volunteers {$where_clause}";
    $stmt = $db->getConnection()->prepare($count_sql);
    $stmt->execute($params);
    $total_volunteers = $stmt->fetch()['total'];
    $total_pages = ceil($total_volunteers / $per_page);
    
    // Get volunteers
    $sql = "SELECT * FROM volunteers {$where_clause} ORDER BY applied_at DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute($params);
    $volunteers = $stmt->fetchAll();
    
    // Get status counts
    $status_counts = $db->fetchAll("SELECT status, COUNT(*) as count FROM volunteers GROUP BY status");
    $counts = [];
    foreach ($status_counts as $row) {
        $counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    $error_message = "Failed to fetch volunteers: " . $e->getMessage();
    $volunteers = [];
    $total_volunteers = 0;
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
                    <i class="fas fa-users me-2"></i>Volunteers Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportVolunteers()">
                            <i class="fas fa-download me-1"></i>Export
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
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($total_volunteers); ?></h5>
                            <p class="card-text text-muted">Total Applications</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['pending'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Pending Review</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['approved'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Approved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['active'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Active Volunteers</p>
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
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, email, phone, skills, or interests...">
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

            <!-- Volunteers Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Volunteer Applications</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($volunteers)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No volunteer applications found</h5>
                            <p class="text-muted">Volunteer applications will appear here when people apply through the website.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Volunteer</th>
                                        <th>Contact</th>
                                        <th>Skills & Interests</th>
                                        <th>Application Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($volunteers as $volunteer): ?>
                                        <tr class="<?php echo $volunteer['status'] === 'pending' ? 'table-warning' : ''; ?>">
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($volunteer['name']); ?></strong>
                                                    <?php if ($volunteer['age']): ?>
                                                        <br><small class="text-muted">Age: <?php echo htmlspecialchars($volunteer['age']); ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($volunteer['occupation']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($volunteer['occupation']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($volunteer['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($volunteer['email']); ?>
                                                </a>
                                                <?php if ($volunteer['phone']): ?>
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-phone fa-xs me-1"></i>
                                                        <?php echo htmlspecialchars($volunteer['phone']); ?>
                                                    </small>
                                                <?php endif; ?>
                                                <?php if ($volunteer['address']): ?>
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-map-marker-alt fa-xs me-1"></i>
                                                        <?php echo htmlspecialchars(substr($volunteer['address'], 0, 30)); ?>...
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($volunteer['skills']): ?>
                                                    <div class="mb-1">
                                                        <strong>Skills:</strong>
                                                        <small class="text-muted d-block">
                                                            <?php echo htmlspecialchars(substr($volunteer['skills'], 0, 50)); ?>
                                                            <?php echo strlen($volunteer['skills']) > 50 ? '...' : ''; ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($volunteer['interests']): ?>
                                                    <div>
                                                        <strong>Interests:</strong>
                                                        <small class="text-muted d-block">
                                                            <?php echo htmlspecialchars(substr($volunteer['interests'], 0, 50)); ?>
                                                            <?php echo strlen($volunteer['interests']) > 50 ? '...' : ''; ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo date('M j, Y', strtotime($volunteer['applied_at'])); ?>
                                                    <br><?php echo date('g:i A', strtotime($volunteer['applied_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($volunteer['status']) {
                                                        'pending' => 'warning',
                                                        'approved' => 'info',
                                                        'active' => 'success',
                                                        'inactive' => 'secondary',
                                                        'rejected' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($volunteer['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="viewVolunteer(<?php echo $volunteer['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="contactVolunteer('<?php echo htmlspecialchars($volunteer['email']); ?>', '<?php echo htmlspecialchars($volunteer['name']); ?>')">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="updateVolunteerStatus(<?php echo $volunteer['id']; ?>, 'approved')">
                                                                <i class="fas fa-check me-2"></i>Approve
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateVolunteerStatus(<?php echo $volunteer['id']; ?>, 'active')">
                                                                <i class="fas fa-user-check me-2"></i>Activate
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateVolunteerStatus(<?php echo $volunteer['id']; ?>, 'inactive')">
                                                                <i class="fas fa-user-times me-2"></i>Deactivate
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateVolunteerStatus(<?php echo $volunteer['id']; ?>, 'rejected')">
                                                                <i class="fas fa-times me-2"></i>Reject
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item" href="#" onclick="addNote(<?php echo $volunteer['id']; ?>)">
                                                                <i class="fas fa-sticky-note me-2"></i>Add Note
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteVolunteer(<?php echo $volunteer['id']; ?>)">
                                                                <i class="fas fa-trash me-2"></i>Delete
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Volunteers pagination">
                                    <ul class="pagination justify-content-center mb-0">
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
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Volunteer View Modal -->
<div class="modal fade" id="volunteerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Volunteer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="volunteerModalBody">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="contactVolunteerButton">
                    <i class="fas fa-envelope me-1"></i>Contact
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_note">
                    <input type="hidden" name="volunteer_id" id="noteVolunteerId">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" id="note" name="note" rows="4" required 
                                  placeholder="Add a note about this volunteer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewVolunteer(volunteerId) {
    fetch(`../api/get_volunteer.php?id=${volunteerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const volunteer = data.volunteer;
                document.getElementById('volunteerModalBody').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Personal Information</h6>
                            <p><strong>Name:</strong> ${volunteer.name}</p>
                            <p><strong>Email:</strong> <a href="mailto:${volunteer.email}">${volunteer.email}</a></p>
                            ${volunteer.phone ? `<p><strong>Phone:</strong> ${volunteer.phone}</p>` : ''}
                            ${volunteer.age ? `<p><strong>Age:</strong> ${volunteer.age}</p>` : ''}
                            ${volunteer.occupation ? `<p><strong>Occupation:</strong> ${volunteer.occupation}</p>` : ''}
                            ${volunteer.address ? `<p><strong>Address:</strong> ${volunteer.address}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            <h6>Application Details</h6>
                            <p><strong>Status:</strong> 
                                <select class="form-select form-select-sm d-inline-block w-auto" onchange="updateVolunteerStatus(${volunteer.id}, this.value)">
                                    <option value="pending" ${volunteer.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="approved" ${volunteer.status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="active" ${volunteer.status === 'active' ? 'selected' : ''}>Active</option>
                                    <option value="inactive" ${volunteer.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                    <option value="rejected" ${volunteer.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                </select>
                            </p>
                            <p><strong>Applied:</strong> ${new Date(volunteer.applied_at).toLocaleString()}</p>
                            ${volunteer.availability ? `<p><strong>Availability:</strong> ${volunteer.availability}</p>` : ''}
                        </div>
                    </div>
                    
                    ${volunteer.skills ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Skills</h6>
                                <p>${volunteer.skills}</p>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${volunteer.interests ? `
                        <div class="row">
                            <div class="col-12">
                                <h6>Areas of Interest</h6>
                                <p>${volunteer.interests}</p>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${volunteer.experience ? `
                        <div class="row">
                            <div class="col-12">
                                <h6>Previous Experience</h6>
                                <p>${volunteer.experience}</p>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${volunteer.motivation ? `
                        <div class="row">
                            <div class="col-12">
                                <h6>Motivation</h6>
                                <p>${volunteer.motivation}</p>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${volunteer.notes ? `
                        <div class="row">
                            <div class="col-12">
                                <h6>Admin Notes</h6>
                                <div class="border rounded p-3 bg-light">
                                    ${volunteer.notes.replace(/\n/g, '<br>')}
                                </div>
                            </div>
                        </div>
                    ` : ''}
                `;
                
                document.getElementById('contactVolunteerButton').onclick = () => {
                    contactVolunteer(volunteer.email, volunteer.name);
                };
                
                new bootstrap.Modal(document.getElementById('volunteerModal')).show();
            } else {
                alert('Failed to load volunteer details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load volunteer details');
        });
}

function updateVolunteerStatus(volunteerId, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('volunteer_id', volunteerId);
    formData.append('status', status);
    
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
        alert('Failed to update status');
    });
}

function contactVolunteer(email, name) {
    const subject = `EYGII Volunteer Application - ${name}`;
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}`;
    window.open(mailtoLink);
}

function addNote(volunteerId) {
    document.getElementById('noteVolunteerId').value = volunteerId;
    new bootstrap.Modal(document.getElementById('addNoteModal')).show();
}

function deleteVolunteer(volunteerId) {
    if (confirm('Are you sure you want to delete this volunteer record? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('volunteer_id', volunteerId);
        
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
            alert('Failed to delete volunteer');
        });
    }
}

function exportVolunteers() {
    window.open('../api/export_volunteers.php', '_blank');
}
</script>

<?php include '../includes/footer.php'; ?> 