<?php
// admin/admin-messages.php
session_start();

// Define database credentials directly
$db_config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'marsilase_pastry'
];

// Create database connection
$conn = new mysqli(
    $db_config['servername'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['dbname']
);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ?page=admin-login');
    exit;
}

// Handle message actions
$message = '';
$message_type = '';

// Mark message as read
if (isset($_POST['action']) && $_POST['action'] === 'mark_as_read') {
    $message_id = $_POST['message_id'];
    
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        $message = "Message marked as read!";
        $message_type = "success";
    } else {
        $message = "Error updating message: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Mark all as read
if (isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE is_read = 0");
    
    if ($stmt->execute()) {
        $message = "All messages marked as read!";
        $message_type = "success";
    } else {
        $message = "Error updating messages: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete message
if (isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    $message_id = $_POST['message_id'];
    
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        $message = "Message deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting message: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete all read messages
if (isset($_POST['action']) && $_POST['action'] === 'delete_all_read') {
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE is_read = 1");
    
    if ($stmt->execute()) {
        $message = "All read messages deleted!";
        $message_type = "success";
    } else {
        $message = "Error deleting messages: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Get message statistics
$stats_result = $conn->query("
    SELECT 
        COUNT(*) as total_messages,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_messages,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_messages
    FROM contact_messages
");
$stats = $stats_result ? $stats_result->fetch_assoc() : ['total_messages' => 0, 'unread_messages' => 0, 'read_messages' => 0];

// Get all messages
$messages_result = $conn->query("
    SELECT * FROM contact_messages 
    ORDER BY created_at DESC
");

$messages = [];
if ($messages_result) {
    $messages = $messages_result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="section">
    <div class="container-narrow">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-4 display-font mb-2">Customer Messages</h1>
                <p class="text-muted">Manage and respond to customer inquiries</p>
            </div>
            <a href="?page=admin-dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="stat-icon" style="background: #339AF0;">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-muted mb-1">Total Messages</h5>
                                <h2 class="mb-0"><?= $stats['total_messages'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="stat-icon" style="background: #FF6B6B;">
                                    <i class="bi bi-envelope"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-muted mb-1">Unread Messages</h5>
                                <h2 class="mb-0"><?= $stats['unread_messages'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="stat-icon" style="background: #51CF66;">
                                    <i class="bi bi-envelope-open"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-muted mb-1">Read Messages</h5>
                                <h2 class="mb-0"><?= $stats['read_messages'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Quick Actions</h6>
                    <div class="btn-group">
                        <form method="POST" class="d-inline me-2">
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-check-all me-1"></i>
                                Mark All as Read
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete all read messages? This action cannot be undone.')">
                            <input type="hidden" name="action" value="delete_all_read">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>
                                Delete All Read
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Messages</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>From</th>
                                <th>Contact</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bi bi-chat-dots display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No messages yet</p>
                                        <p class="text-muted small">Customer messages will appear here when they contact you through the contact form.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                <tr class="<?= !$msg['is_read'] ? 'table-warning' : '' ?>">
                                    <td>
                                        <?php if (!$msg['is_read']): ?>
                                            <span class="badge bg-warning">New</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($msg['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($msg['subject'] ?? 'No Subject') ?></small>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-primary"><?= htmlspecialchars($msg['email']) ?></small>
                                            <?php if (!empty($msg['phone'])): ?>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($msg['phone']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="message-preview">
                                            <?= htmlspecialchars(substr($msg['message'], 0, 100)) ?>
                                            <?= strlen($msg['message']) > 100 ? '...' : '' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($msg['created_at'])) ?><br>
                                            <small><?= date('g:i A', strtotime($msg['created_at'])) ?></small>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewMessage(<?= $msg['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <?php if (!$msg['is_read']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="mark_as_read">
                                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-success" title="Mark as Read">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                <input type="hidden" name="action" value="delete_message">
                                                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message Details Modal -->
<div class="modal fade" id="messageDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageDetailsContent">
                <!-- Content loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="replyToMessage()">
                    <i class="bi bi-reply me-1"></i>
                    Reply via Email
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: linear-gradient(135deg, #FFFFFF 0%, #FFE8D6 100%);
    border-radius: 16px;
    border: none;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 145, 77, 0.2);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.message-preview {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}
</style>

<script>
function viewMessage(messageId) {
    // Show loading state
    document.getElementById('messageDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Create AJAX request to get message details
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?page=admin-get-message&message_id=${messageId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('messageDetailsContent').innerHTML = xhr.responseText;
            
            // Mark as read when viewing
            markMessageAsRead(messageId);
        } else {
            document.getElementById('messageDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    Failed to load message details. Please try again.
                </div>
            `;
        }
    };
    xhr.onerror = function() {
        document.getElementById('messageDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                Network error. Please check your connection.
            </div>
        `;
    };
    xhr.send();
    
    // Show modal
    new bootstrap.Modal(document.getElementById('messageDetailsModal')).show();
}

function markMessageAsRead(messageId) {
    // Send AJAX request to mark message as read
    const formData = new FormData();
    formData.append('action', 'mark_as_read');
    formData.append('message_id', messageId);
    
    fetch('?page=admin-messages', {
        method: 'POST',
        body: formData
    });
}

function replyToMessage() {
    const messageContent = document.getElementById('messageDetailsContent');
    const emailElement = messageContent.querySelector('.message-email');
    
    if (emailElement) {
        const email = emailElement.textContent.trim();
        window.open(`mailto:${email}`, '_blank');
    } else {
        alert('Email address not found.');
    }
}
</script>

<?php 
// Close connection
$conn->close();
?>