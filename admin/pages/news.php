<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$page_title = "News Management";
$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_news') {
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $author = $_SESSION['admin_username'] ?? 'Admin';
        
        if (!empty($title) && !empty($excerpt) && !empty($content)) {
            try {
                // Handle image upload
                $image_path = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../../assets/images/news/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $filename = 'news_' . time() . '_' . uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_path = 'assets/images/news/' . $filename;
                        }
                    }
                }
                
                $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
                
                $stmt = $db->getConnection()->prepare("INSERT INTO news (title, excerpt, content, image_path, status, author, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $excerpt, $content, $image_path, $status, $author, $published_at, date('Y-m-d H:i:s')]);
                $success_message = "News article added successfully.";
            } catch (Exception $e) {
                $error_message = "Failed to add news article: " . $e->getMessage();
            }
        } else {
            $error_message = "Title, excerpt, and content are required.";
        }
    }
    
    if ($action === 'edit_news') {
        $news_id = $_POST['news_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        
        if (!empty($news_id) && !empty($title) && !empty($excerpt) && !empty($content)) {
            try {
                // Get current news data
                $current_news = $db->fetch("SELECT * FROM news WHERE id = ?", [$news_id]);
                
                if ($current_news) {
                    $image_path = $current_news['image_path'];
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../../assets/images/news/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        
                        if (in_array($file_extension, $allowed_extensions)) {
                            $filename = 'news_' . time() . '_' . uniqid() . '.' . $file_extension;
                            $target_path = $upload_dir . $filename;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                                // Delete old image if exists
                                if ($image_path && file_exists('../../' . $image_path)) {
                                    unlink('../../' . $image_path);
                                }
                                $image_path = 'assets/images/news/' . $filename;
                            }
                        }
                    }
                    
                    // Update published_at if status changed to published
                    $published_at = $current_news['published_at'];
                    if ($status === 'published' && $current_news['status'] !== 'published') {
                        $published_at = date('Y-m-d H:i:s');
                    } elseif ($status !== 'published') {
                        $published_at = null;
                    }
                    
                    $stmt = $db->getConnection()->prepare("UPDATE news SET title = ?, excerpt = ?, content = ?, image_path = ?, status = ?, published_at = ?, updated_at = ? WHERE id = ?");
                    $stmt->execute([$title, $excerpt, $content, $image_path, $status, $published_at, date('Y-m-d H:i:s'), $news_id]);
                    $success_message = "News article updated successfully.";
                } else {
                    $error_message = "News article not found.";
                }
            } catch (Exception $e) {
                $error_message = "Failed to update news article: " . $e->getMessage();
            }
        } else {
            $error_message = "All required fields must be filled.";
        }
    }
    
    if ($action === 'delete_news') {
        $news_id = $_POST['news_id'] ?? '';
        
        try {
            // Get news data to delete image
            $news = $db->fetch("SELECT image_path FROM news WHERE id = ?", [$news_id]);
            
            if ($news) {
                // Delete image file if exists
                if ($news['image_path'] && file_exists('../../' . $news['image_path'])) {
                    unlink('../../' . $news['image_path']);
                }
                
                $stmt = $db->getConnection()->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$news_id]);
                $success_message = "News article deleted successfully.";
            } else {
                $error_message = "News article not found.";
            }
        } catch (Exception $e) {
            $error_message = "Failed to delete news article: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_status') {
        $news_id = $_POST['news_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        
        try {
            $current_news = $db->fetch("SELECT status FROM news WHERE id = ?", [$news_id]);
            
            if ($current_news) {
                $published_at = null;
                if ($new_status === 'published' && $current_news['status'] !== 'published') {
                    $published_at = date('Y-m-d H:i:s');
                } elseif ($new_status === 'published') {
                    // Keep existing published_at
                    $published_at = $db->fetch("SELECT published_at FROM news WHERE id = ?", [$news_id])['published_at'];
                }
                
                $stmt = $db->getConnection()->prepare("UPDATE news SET status = ?, published_at = ?, updated_at = ? WHERE id = ?");
                $stmt->execute([$new_status, $published_at, date('Y-m-d H:i:s'), $news_id]);
                $success_message = "News status updated successfully.";
            }
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
    $where_conditions[] = "(title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR author LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get news with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM news {$where_clause}";
    $stmt = $db->getConnection()->prepare($count_sql);
    $stmt->execute($params);
    $total_news = $stmt->fetch()['total'];
    $total_pages = ceil($total_news / $per_page);
    
    // Get news
    $sql = "SELECT * FROM news {$where_clause} ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute($params);
    $news_articles = $stmt->fetchAll();
    
    // Get status counts
    $status_counts = $db->fetchAll("SELECT status, COUNT(*) as count FROM news GROUP BY status");
    $counts = [];
    foreach ($status_counts as $row) {
        $counts[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    $error_message = "Failed to fetch news: " . $e->getMessage();
    $news_articles = [];
    $total_news = 0;
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
                    <i class="fas fa-newspaper me-2"></i>News Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                            <i class="fas fa-plus me-1"></i>Add News
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
                                <i class="fas fa-newspaper fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($total_news); ?></h5>
                            <p class="card-text text-muted">Total Articles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['published'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Published</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-edit fa-2x"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($counts['draft'] ?? 0); ?></h5>
                            <p class="card-text text-muted">Drafts</p>
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
                                <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by title, excerpt, content, or author...">
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

            <!-- News Articles -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">News Articles</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($news_articles)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No news articles found</h5>
                            <p class="text-muted">Create your first news article to get started.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                                <i class="fas fa-plus me-1"></i>Add News
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Article</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Published Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($news_articles as $article): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <?php if ($article['image_path']): ?>
                                                        <img src="../../<?php echo htmlspecialchars($article['image_path']); ?>" 
                                                             class="me-3 rounded" style="width: 60px; height: 60px; object-fit: cover;" 
                                                             alt="<?php echo htmlspecialchars($article['title']); ?>">
                                                    <?php else: ?>
                                                        <div class="me-3 bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($article['title']); ?></h6>
                                                        <p class="text-muted mb-0 small">
                                                            <?php echo htmlspecialchars(substr($article['excerpt'], 0, 100)); ?>
                                                            <?php echo strlen($article['excerpt']) > 100 ? '...' : ''; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-medium"><?php echo htmlspecialchars($article['author']); ?></span>
                                                <br><small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($article['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($article['status']) {
                                                        'published' => 'success',
                                                        'draft' => 'warning',
                                                        'archived' => 'secondary',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($article['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($article['published_at']): ?>
                                                    <small>
                                                        <?php echo date('M j, Y', strtotime($article['published_at'])); ?>
                                                        <br><?php echo date('g:i A', strtotime($article['published_at'])); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">Not published</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="editNews(<?php echo $article['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="viewNews(<?php echo $article['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="updateNewsStatus(<?php echo $article['id']; ?>, 'published')">
                                                                <i class="fas fa-check me-2"></i>Publish
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateNewsStatus(<?php echo $article['id']; ?>, 'draft')">
                                                                <i class="fas fa-edit me-2"></i>Draft
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateNewsStatus(<?php echo $article['id']; ?>, 'archived')">
                                                                <i class="fas fa-archive me-2"></i>Archive
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteNews(<?php echo $article['id']; ?>)">
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
                                <nav aria-label="News pagination">
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

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add News Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_news">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Article Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Excerpt *</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required 
                                          placeholder="Brief summary of the article..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="10" required 
                                          placeholder="Full article content..."></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="image" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WebP</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Article</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit News Modal -->
<div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit News Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body" id="editNewsModalBody">
                    <!-- Content loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Article</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View News Modal -->
<div class="modal fade" id="viewNewsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Article Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewNewsModalBody">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function editNews(newsId) {
    fetch(`../api/get_news.php?id=${newsId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const news = data.news;
                document.getElementById('editNewsModalBody').innerHTML = `
                    <input type="hidden" name="action" value="edit_news">
                    <input type="hidden" name="news_id" value="${news.id}">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">Article Title *</label>
                                <input type="text" class="form-control" id="edit_title" name="title" value="${news.title}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_excerpt" class="form-label">Excerpt *</label>
                                <textarea class="form-control" id="edit_excerpt" name="excerpt" rows="3" required>${news.excerpt}</textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_content" class="form-label">Content *</label>
                                <textarea class="form-control" id="edit_content" name="content" rows="10" required>${news.content}</textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            ${news.image_path ? `
                                <div class="mb-3">
                                    <label class="form-label">Current Image</label>
                                    <div>
                                        <img src="../../${news.image_path}" class="img-thumbnail" style="max-height: 150px;">
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
                                    <option value="draft" ${news.status === 'draft' ? 'selected' : ''}>Draft</option>
                                    <option value="published" ${news.status === 'published' ? 'selected' : ''}>Published</option>
                                    <option value="archived" ${news.status === 'archived' ? 'selected' : ''}>Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
                
                new bootstrap.Modal(document.getElementById('editNewsModal')).show();
            } else {
                alert('Failed to load news article');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load news article');
        });
}

function viewNews(newsId) {
    fetch(`../api/get_news.php?id=${newsId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const news = data.news;
                document.getElementById('viewNewsModalBody').innerHTML = `
                    <article>
                        ${news.image_path ? `
                            <img src="../../${news.image_path}" class="img-fluid rounded mb-4" alt="${news.title}">
                        ` : ''}
                        
                        <h2>${news.title}</h2>
                        
                        <div class="text-muted mb-3">
                            <small>
                                By ${news.author} • 
                                ${news.published_at ? new Date(news.published_at).toLocaleDateString() : 'Not published'} • 
                                <span class="badge bg-${news.status === 'published' ? 'success' : news.status === 'draft' ? 'warning' : 'secondary'}">${news.status.charAt(0).toUpperCase() + news.status.slice(1)}</span>
                            </small>
                        </div>
                        
                        <div class="lead mb-4">${news.excerpt}</div>
                        
                        <div class="content">
                            ${news.content.replace(/\n/g, '<br>')}
                        </div>
                    </article>
                `;
                
                new bootstrap.Modal(document.getElementById('viewNewsModal')).show();
            } else {
                alert('Failed to load news article');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load news article');
        });
}

function updateNewsStatus(newsId, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('news_id', newsId);
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

function deleteNews(newsId) {
    if (confirm('Are you sure you want to delete this news article? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_news');
        formData.append('news_id', newsId);
        
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
            alert('Failed to delete news article');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?> 