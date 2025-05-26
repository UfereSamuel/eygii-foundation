<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Contact Messages";
$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $contact_id = $_POST['contact_id'] ?? '';
    
    if ($action === 'update_status' && $contact_id) {
        $new_status = $_POST['status'] ?? '';
        try {
            $stmt = $db->getConnection()->prepare("UPDATE contact_submissions SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, date('Y-m-d H:i:s'), $contact_id]);
            $success_message = "Contact status updated successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to update status: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete' && $contact_id) {
        try {
            $stmt = $db->getConnection()->prepare("DELETE FROM contact_submissions WHERE id = ?");
            $stmt->execute([$contact_id]);
            $success_message = "Contact message deleted successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to delete message: " . $e->getMessage();
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
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get contacts with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM contact_submissions {$where_clause}";
    $stmt = $db->getConnection()->prepare($count_sql);
    $stmt->execute($params);
    $total_contacts = $stmt->fetch()['total'];
    $total_pages = ceil($total_contacts / $per_page);
    
    // Get contacts
    $sql = "SELECT * FROM contact_submissions {$where_clause} ORDER BY submitted_at DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll();
    
    // Get status counts
    $status_counts = $db->fetchAll("SELECT status, COUNT(*) as count FROM contact_submissions GROUP BY status");
    $counts = [];
    foreach ($status_counts as $row) {
        $counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    $error_message = "Failed to fetch contacts: " . $e->getMessage();
    $contacts = [];
    $total_contacts = 0;
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
                    <i class="fas fa-envelope me-2"></i>Contact Messages
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportContacts()">
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
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($total_contacts); ?></h5>
                            <p class="card-text text-muted">Total Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['new'] ?? 0); ?></h5>
                            <p class="card-text text-muted">New Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-eye fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['read'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Read Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-reply fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['replied'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Replied</p>
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
                                <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, email, subject, or message...">
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

            <!-- Contact Messages Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Contact Messages</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($contacts)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No contact messages found</h5>
                            <p class="text-muted">Contact messages will appear here when visitors submit the contact form.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr class="<?php echo $contact['status'] === 'new' ? 'table-warning' : ''; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($contact['name']); ?></strong>
                                                        <?php if ($contact['organization']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($contact['organization']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($contact['email']); ?>
                                                </a>
                                                <?php if ($contact['phone']): ?>
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-phone fa-xs me-1"></i>
                                                        <?php echo htmlspecialchars($contact['phone']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="fw-medium"><?php echo htmlspecialchars($contact['subject']); ?></span>
                                                <br><small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($contact['message'], 0, 100)); ?>
                                                    <?php echo strlen($contact['message']) > 100 ? '...' : ''; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo date('M j, Y', strtotime($contact['submitted_at'])); ?>
                                                    <br><?php echo date('g:i A', strtotime($contact['submitted_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($contact['status']) {
                                                        'new' => 'warning',
                                                        'read' => 'info',
                                                        'replied' => 'success',
                                                        'closed' => 'secondary',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($contact['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="viewContact(<?php echo $contact['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="replyToContact('<?php echo htmlspecialchars($contact['email']); ?>', '<?php echo htmlspecialchars($contact['subject']); ?>')">
                                                        <i class="fas fa-reply"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteContact(<?php echo $contact['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                                <nav aria-label="Contact messages pagination">
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

<!-- Contact View Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contactModalBody">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="replyButton">
                    <i class="fas fa-reply me-1"></i>Reply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewContact(contactId) {
    fetch(`../api/get_contact.php?id=${contactId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.contact;
                document.getElementById('contactModalBody').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Contact Information</h6>
                            <p><strong>Name:</strong> ${contact.name}</p>
                            <p><strong>Email:</strong> <a href="mailto:${contact.email}">${contact.email}</a></p>
                            ${contact.phone ? `<p><strong>Phone:</strong> ${contact.phone}</p>` : ''}
                            ${contact.organization ? `<p><strong>Organization:</strong> ${contact.organization}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            <h6>Message Details</h6>
                            <p><strong>Subject:</strong> ${contact.subject}</p>
                            <p><strong>Date:</strong> ${new Date(contact.submitted_at).toLocaleString()}</p>
                            <p><strong>Status:</strong> 
                                <select class="form-select form-select-sm d-inline-block w-auto" onchange="updateContactStatus(${contact.id}, this.value)">
                                    <option value="new" ${contact.status === 'new' ? 'selected' : ''}>New</option>
                                    <option value="read" ${contact.status === 'read' ? 'selected' : ''}>Read</option>
                                    <option value="replied" ${contact.status === 'replied' ? 'selected' : ''}>Replied</option>
                                    <option value="closed" ${contact.status === 'closed' ? 'selected' : ''}>Closed</option>
                                </select>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6>Message</h6>
                            <div class="border rounded p-3 bg-light">
                                ${contact.message.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('replyButton').onclick = () => {
                    replyToContact(contact.email, contact.subject);
                };
                
                // Mark as read if it's new
                if (contact.status === 'new') {
                    updateContactStatus(contact.id, 'read');
                }
                
                new bootstrap.Modal(document.getElementById('contactModal')).show();
            } else {
                alert('Failed to load contact details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load contact details');
        });
}

function updateContactStatus(contactId, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('contact_id', contactId);
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

function replyToContact(email, subject) {
    const replySubject = subject.startsWith('Re:') ? subject : `Re: ${subject}`;
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(replySubject)}`;
    window.open(mailtoLink);
}

function deleteContact(contactId) {
    if (confirm('Are you sure you want to delete this contact message? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('contact_id', contactId);
        
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
            alert('Failed to delete contact');
        });
    }
}

function exportContacts() {
    window.open('../api/export_contacts.php', '_blank');
}
</script>

<?php include '../includes/footer.php'; ?> 