<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow">
                    <div class="card-body py-5">
                        <i class="bi bi-shield-exclamation display-1 text-danger mb-4"></i>
                        <h2 class="text-danger mb-3">Access Denied</h2>
                        <p class="text-muted mb-4">
                            You don't have permission to access this page.
                        </p>
                        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin'): ?>
                        <a href="admin/index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Admin Panel
                        </a>
                        <?php else: ?>
                        <a href="owner/index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Owner Panel
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
