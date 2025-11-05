<?php
// admin/messages.php
require_once 'config.php';
requireAdminAuth();

// Handle message actions
if (isset($_POST['update_status'])) {
    $message_id = $_POST['message_id'];
    $new_status = $_POST['status'];
    $admin_notes = sanitizeInput($_POST['admin_notes'] ?? '');
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $new_status, $admin_notes, $message_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Message status updated successfully!";
        logAdminAction('Message Status Update', "Updated message #$message_id to $new_status");
    } else {
        $_SESSION['error_message'] = "Error updating message status.";
    }
    $stmt->close();
}

if (isset($_GET['delete_id'])) {
    $message_id = $_GET['delete_id'];
    
    if ($conn->query("DELETE FROM contact_messages WHERE id = $message_id")) {
        $_SESSION['success_message'] = "Message deleted successfully!";
        logAdminAction('Message Deleted', "Deleted message #$message_id");
    } else {
        $_SESSION['error_message'] = "Error deleting message.";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';

// Build query
$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search_term)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_like = "%$search_term%";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= 'ssss';
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get message statistics
$total_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count'];
$unread_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'")->fetch_assoc()['count'];
$read_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'read'")->fetch_assoc()['count'];
$replied_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Management - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .message-card {
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .message-card.unread {
            border-left-color: #f56e10;
            background-color: #fff8f0;
        }
        
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .message-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.active {
            border-color: #f56e10;
            background-color: #fff8f0;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Message Management</h2>
                <p class="text-muted mb-0">Manage customer inquiries and messages</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>

        <!-- Message Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card <?= $status_filter === 'all' ? 'active' : '' ?>" 
                     onclick="window.location.href='messages.php'">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-chat-left-text text-primary fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $total_messages ?></h4>
                            <span class="text-muted">Total Messages</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card <?= $status_filter === 'unread' ? 'active' : '' ?>" 
                     onclick="window.location.href='messages.php?status=unread'">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-envelope text-warning fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $unread_messages ?></h4>
                            <span class="text-muted">Unread</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card <?= $status_filter === 'read' ? 'active' : '' ?>" 
                     onclick="window.location.href='messages.php?status=read'">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-envelope-open text-info fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $read_messages ?></h4>
                            <span class="text-muted">Read</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card <?= $status_filter === 'replied' ? 'active' : '' ?>" 
                     onclick="window.location.href='messages.php?status=replied'">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-reply text-success fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $replied_messages ?></h4>
                            <span class="text-muted">Replied</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status Filter</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Messages</option>
                            <option value="unread" <?= $status_filter === 'unread' ? 'selected' : '' ?>>Unread Only</option>
                            <option value="read" <?= $status_filter === 'read' ? 'selected' : '' ?>>Read Only</option>
                            <option value="replied" <?= $status_filter === 'replied' ? 'selected' : '' ?>>Replied Only</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Search Messages</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name, email, subject, or message..." 
                                   value="<?= htmlspecialchars($search_term) ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if (!empty($search_term) || $status_filter !== 'all'): ?>
                            <a href="messages.php" class="btn btn-outline-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <!-- Messages List -->
        <div class="row">
            <?php foreach ($messages as $message): ?>
            <div class="col-12 mb-3">
                <div class="card message-card <?= $message['status'] === 'unread' ? 'unread' : '' ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($message['name']) ?></h6>
                                        <p class="text-muted mb-1">
                                            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($message['email']) ?>
                                            <?php if ($message['phone']): ?>
                                            • <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($message['phone']) ?>
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($message['subject']): ?>
                                        <h6 class="text-primary mb-1"><?= htmlspecialchars($message['subject']) ?></h6>
                                        <?php endif; ?>
                                        <p class="text-muted message-preview mb-0">
                                            <?= htmlspecialchars(mb_strimwidth($message['message'], 0, 150, '...')) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-<?= 
                                            $message['status'] === 'unread' ? 'warning' : 
                                            ($message['status'] === 'replied' ? 'success' : 'info')
                                        ?>">
                                            <?= ucfirst($message['status']) ?>
                                        </span>
                                        <div class="text-muted small mt-1">
                                            <?= date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#messageModal"
                                                onclick="viewMessage(<?= htmlspecialchars(json_encode($message)) ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="?delete_id=<?= $message['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this message?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($messages)): ?>
        <div class="text-center py-5">
            <i class="bi bi-chat-left-text display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">No messages found</h4>
            <p class="text-muted">
                <?= empty($search_term) && $status_filter === 'all' ? 'Messages from customers will appear here.' : 'No messages match your current filters.' ?>
            </p>
            <?php if (!empty($search_term) || $status_filter !== 'all'): ?>
            <a href="messages.php" class="btn btn-primary">Clear Filters</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Message Details Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="messageForm">
                    <input type="hidden" name="message_id" id="messageId">
                    <div class="modal-header">
                        <h5 class="modal-title">Message Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="messageContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                    <div class="modal-footer">
                        <div class="me-auto">
                            <select name="status" class="form-select form-select-sm" id="statusSelect" style="width: auto; display: inline-block;">
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                                <option value="replied">Replied</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewMessage(message) {
            document.getElementById('messageId').value = message.id;
            document.getElementById('statusSelect').value = message.status;
            
            const messageContent = document.getElementById('messageContent');
            messageContent.innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Sender Information</h6>
                        <p><strong>Name:</strong> ${message.name}</p>
                        <p><strong>Email:</strong> <a href="mailto:${message.email}">${message.email}</a></p>
                        ${message.phone ? `<p><strong>Phone:</strong> <a href="tel:${message.phone}">${message.phone}</a></p>` : ''}
                        <p><strong>Sent:</strong> ${new Date(message.created_at).toLocaleString()}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Message Status</h6>
                        <p><strong>Current Status:</strong> <span class="badge bg-${getStatusColor(message.status)}">${message.status}</span></p>
                        ${message.updated_at && message.updated_at !== message.created_at ? 
                          `<p><strong>Last Updated:</strong> ${new Date(message.updated_at).toLocaleString()}</p>` : ''}
                    </div>
                </div>
                
                ${message.subject ? `<h6>Subject</h6><p class="mb-3 fw-semibold">${message.subject}</p>` : ''}
                
                <h6>Message</h6>
                <div class="border rounded p-3 bg-light mb-3">
                    ${message.message.replace(/\n/g, '<br>')}
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Admin Notes</label>
                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add internal notes about this message...">${message.admin_notes || ''}</textarea>
                </div>
                
                <div class="mt-4">
                    <a href="mailto:${message.email}?subject=Re: ${message.subject || 'Your Message'}" class="btn btn-primary">
                        <i class="bi bi-reply me-2"></i>Reply via Email
                    </a>
                </div>
            `;
        }
        
        function getStatusColor(status) {
            switch(status) {
                case 'unread': return 'warning';
                case 'read': return 'info';
                case 'replied': return 'success';
                default: return 'secondary';
            }
        }
        
        // Auto-submit form when status changes
        document.getElementById('statusSelect').addEventListener('change', function() {
            document.getElementById('messageForm').submit();
        });
    </script>
</body>
</html>