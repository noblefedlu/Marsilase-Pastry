<?php
session_start();
include './common/connection.php';
requireRole('admin');

// Mark notifications as read
if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
    header('Location: notifications.php');
    exit;
}

// Get notifications
$notifications = $conn->query("
    SELECT * FROM admin_notifications 
    ORDER BY created_at DESC 
    LIMIT 50
")->fetch_all(MYSQLI_ASSOC);

$unread_count = getUnreadNotificationCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'styles.php'; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-bell me-2"></i>Notifications
            </span>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="nav-link me-3">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <?php if ($unread_count > 0): ?>
                <a href="notifications.php?mark_read=1" class="nav-link me-3">
                    <i class="bi bi-check-all me-1"></i>Mark All Read
                </a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">System Notifications</h2>
            <?php if ($unread_count > 0): ?>
            <span class="badge bg-primary fs-6"><?= $unread_count ?> unread</span>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications</h5>
                    <p class="text-muted">You're all caught up!</p>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item <?= $notification['is_read'] ? '' : 'bg-light' ?>">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 <?= $notification['is_read'] ? 'text-muted' : 'text-dark' ?>">
                                        <?= $notification['message'] ?>
                                    </h6>
                                    <small class="text-muted ms-2">
                                        <?= date('M j, g:i A', strtotime($notification['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">
                                        <?= ucfirst(str_replace('_', ' ', $notification['type'])) ?>
                                    </span>
                                    <?php if (!$notification['is_read']): ?>
                                    <span class="badge bg-primary">New</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>