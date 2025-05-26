<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Newsletter Subscribers";
$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $subscriber_id = $_POST['subscriber_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        
        try {
            $stmt = $db->getConnection()->prepare("UPDATE newsletter_subscribers SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, date('Y-m-d H:i:s'), $subscriber_id]);
            $success_message = "Subscriber status updated successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to update status: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete') {
        $subscriber_id = $_POST['subscriber_id'] ?? '';
        
        try {
            $stmt = $db->getConnection()->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
            $stmt->execute([$subscriber_id]);
            $success_message = "Subscriber deleted successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to delete subscriber: " . $e->getMessage();
        }
    }
    
    if ($action === 'bulk_action') {
        $bulk_action = $_POST['bulk_action'] ?? '';
        $selected_ids = $_POST['selected_ids'] ?? [];
        
        if (!empty($selected_ids) && $bulk_action) {
            $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
            
            try {
                if ($bulk_action === 'activate') {
                    $stmt = $db->getConnection()->prepare("UPDATE newsletter_subscribers SET status = 'active', updated_at = ? WHERE id IN ($placeholders)");
                    $params = array_merge([date('Y-m-d H:i:s')], $selected_ids);
                    $stmt->execute($params);
                    $success_message = count($selected_ids) . " subscribers activated successfully.";
                } elseif ($bulk_action === 'deactivate') {
                    $stmt = $db->getConnection()->prepare("UPDATE newsletter_subscribers SET status = 'inactive', updated_at = ? WHERE id IN ($placeholders)");
                    $params = array_merge([date('Y-m-d H:i:s')], $selected_ids);
                    $stmt->execute($params);
                    $success_message = count($selected_ids) . " subscribers deactivated successfully.";
                } elseif ($bulk_action === 'delete') {
                    $stmt = $db->getConnection()->prepare("DELETE FROM newsletter_subscribers WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    $success_message = count($selected_ids) . " subscribers deleted successfully.";
                }
            } catch (Exception $e) {
                $error_message = "Bulk action failed: " . $e->getMessage();
            }
        }
    }
    
    if ($action === 'add_subscriber') {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                // Check if email already exists
                $existing = $db->fetch("SELECT id FROM newsletter_subscribers WHERE email = ?", [$email]);
                
                if ($existing) {
                    $error_message = "Email address already exists in the newsletter list.";
                } else {
                    $stmt = $db->getConnection()->prepare("INSERT INTO newsletter_subscribers (email, name, status, subscribed_at) VALUES (?, ?, 'active', ?)");
                    $stmt->execute([$email, $name, date('Y-m-d H:i:s')]);
                    $success_message = "Subscriber added successfully.";
                }
            } catch (Exception $e) {
                $error_message = "Failed to add subscriber: " . $e->getMessage();
            }
        } else {
            $error_message = "Please enter a valid email address.";
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
    $where_conditions[] = "(email LIKE ? OR name LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_merge($params, [$search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get subscribers with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM newsletter_subscribers {$where_clause}";
    $stmt = $db->getConnection()->prepare($count_sql);
    $stmt->execute($params);
    $total_subscribers = $stmt->fetch()['total'];
    $total_pages = ceil($total_subscribers / $per_page);
    
    // Get subscribers
    $sql = "SELECT * FROM newsletter_subscribers {$where_clause} ORDER BY subscribed_at DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute($params);
    $subscribers = $stmt->fetchAll();
    
    // Get status counts
    $status_counts = $db->fetchAll("SELECT status, COUNT(*) as count FROM newsletter_subscribers GROUP BY status");
    $counts = [];
    foreach ($status_counts as $row) {
        $counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    $error_message = "Failed to fetch subscribers: " . $e->getMessage();
    $subscribers = [];
    $total_subscribers = 0;
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
                    <i class="fas fa-newspaper me-2"></i>Newsletter Subscribers
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
                            <i class="fas fa-plus me-1"></i>Add Subscriber
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportSubscribers()">
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
                            <h5 class="card-title"><?php echo number_format($total_subscribers); ?></h5>
                            <p class="card-text text-muted">Total Subscribers</p>
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
                            <h5 class="card-title"><?php echo number_format($counts['inactive'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Inactive</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-ban fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['unsubscribed'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Unsubscribed</p>
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
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="unsubscribed" <?php echo $status_filter === 'unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by email or name...">
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

            <!-- Subscribers Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Newsletter Subscribers</h5>
                    <div class="d-flex align-items-center">
                        <select class="form-select form-select-sm me-2" id="bulkAction" style="width: auto;">
                            <option value="">Bulk Actions</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="applyBulkAction()">Apply</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($subscribers)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No subscribers found</h5>
                            <p class="text-muted">Newsletter subscribers will appear here when visitors subscribe.</p>
                        </div>
                    <?php else: ?>
                        <form id="bulkForm" method="POST">
                            <input type="hidden" name="action" value="bulk_action">
                            <input type="hidden" name="bulk_action" id="bulkActionInput">
                            
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" class="form-check-input" id="selectAll">
                                            </th>
                                            <th>Email</th>
                                            <th>Name</th>
                                            <th>Subscribed Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subscribers as $subscriber): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input subscriber-checkbox" 
                                                           name="selected_ids[]" value="<?php echo $subscriber['id']; ?>">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($subscriber['email']); ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo $subscriber['name'] ? htmlspecialchars($subscriber['name']) : '<span class="text-muted">Not provided</span>'; ?>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('M j, Y', strtotime($subscriber['subscribed_at'])); ?>
                                                        <br><?php echo date('g:i A', strtotime($subscriber['subscribed_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($subscriber['status']) {
                                                            'active' => 'success',
                                                            'inactive' => 'warning',
                                                            'unsubscribed' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($subscriber['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <select class="form-select form-select-sm" 
                                                                onchange="updateSubscriberStatus(<?php echo $subscriber['id']; ?>, this.value)"
                                                                style="width: auto;">
                                                            <option value="active" <?php echo $subscriber['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                            <option value="inactive" <?php echo $subscriber['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                            <option value="unsubscribed" <?php echo $subscriber['status'] === 'unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
                                                        </select>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                onclick="deleteSubscriber(<?php echo $subscriber['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Subscribers pagination">
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

<!-- Add Subscriber Modal -->
<div class="modal fade" id="addSubscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subscriber</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_subscriber">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name (Optional)</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.subscriber-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function updateSubscriberStatus(subscriberId, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('subscriber_id', subscriberId);
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

function deleteSubscriber(subscriberId) {
    if (confirm('Are you sure you want to delete this subscriber? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('subscriber_id', subscriberId);
        
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
            alert('Failed to delete subscriber');
        });
    }
}

function applyBulkAction() {
    const bulkAction = document.getElementById('bulkAction').value;
    const selectedCheckboxes = document.querySelectorAll('.subscriber-checkbox:checked');
    
    if (!bulkAction) {
        alert('Please select a bulk action');
        return;
    }
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one subscriber');
        return;
    }
    
    const actionText = bulkAction === 'delete' ? 'delete' : bulkAction;
    if (confirm(`Are you sure you want to ${actionText} ${selectedCheckboxes.length} selected subscriber(s)?`)) {
        document.getElementById('bulkActionInput').value = bulkAction;
        document.getElementById('bulkForm').submit();
    }
}

function exportSubscribers() {
    window.open('../api/export_subscribers.php', '_blank');
}
</script>

<?php include '../includes/footer.php'; ?> 