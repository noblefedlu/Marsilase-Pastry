<?php
session_start();

// Define the root directory and config path
$root_dir = dirname(dirname(__FILE__));
$config_path = $root_dir . '/config.php';

// Check if config file exists before requiring it
if (!file_exists($config_path)) {
    die("Configuration file not found. Please check if config.php exists in the root directory.");
}

require_once $config_path;

// Check database connection
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../?page=admin-login');
    exit;
}

$message = '';
$error = '';

// Handle content updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_settings') {
            // Update site settings
            $settings = [
                'site_name' => $_POST['site_name'],
                'site_email' => $_POST['site_email'],
                'site_phone' => $_POST['site_phone'],
                'site_address' => $_POST['site_address'],
                'facebook_url' => $_POST['facebook_url'],
                'instagram_url' => $_POST['instagram_url'],
                'twitter_url' => $_POST['twitter_url'],
                'about_text' => $_POST['about_text'],
                'delivery_info' => $_POST['delivery_info'],
                'privacy_policy' => $_POST['privacy_policy'],
                'terms_conditions' => $_POST['terms_conditions']
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                      ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->bind_param("sss", $key, $value, $value);
                $stmt->execute();
                $stmt->close();
            }
            
            $message = "Site settings updated successfully!";
        }
        
        if ($_POST['action'] === 'upload_banner') {
            // Handle banner image upload
            $banner_type = $_POST['banner_type'];
            $image_url = $_POST['image_url'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $conn->prepare("INSERT INTO banners (banner_type, image_url, title, description, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $banner_type, $image_url, $title, $description, $is_active);
            
            if ($stmt->execute()) {
                $message = "Banner uploaded successfully!";
            } else {
                $error = "Failed to upload banner: " . $stmt->error;
            }
            $stmt->close();
        }
        
        if ($_POST['action'] === 'update_banner_status') {
            $banner_id = $_POST['banner_id'];
            $is_active = $_POST['is_active'];
            
            $stmt = $conn->prepare("UPDATE banners SET is_active = ? WHERE id = ?");
            $stmt->bind_param("ii", $is_active, $banner_id);
            $stmt->execute();
            $stmt->close();
            
            $message = "Banner status updated!";
        }
    }
}

// Get current settings
$settings_result = $conn->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get banners
$banners = $conn->query("SELECT * FROM banners ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Get pages content
$pages = [
    'home' => 'Home Page',
    'about' => 'About Us',
    'contact' => 'Contact Us',
    'products' => 'Products Page'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/your-tinymce-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Content Management</h1>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Settings Tabs -->
                <ul class="nav nav-tabs mb-4" id="contentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab">
                            <i class="bi bi-gear me-1"></i>Site Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="banners-tab" data-bs-toggle="tab" data-bs-target="#banners" type="button" role="tab">
                            <i class="bi bi-image me-1"></i>Banners & Sliders
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" type="button" role="tab">
                            <i class="bi bi-file-text me-1"></i>Page Content
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                            <i class="bi bi-search me-1"></i>SEO Settings
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="contentTabsContent">
                    <!-- Site Settings Tab -->
                    <div class="tab-pane fade show active" id="site" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Site Information & Configuration</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Site Name</label>
                                                <input type="text" class="form-control" name="site_name" 
                                                       value="<?= htmlspecialchars($settings['site_name'] ?? 'Marsilase Pastry') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Contact Email</label>
                                                <input type="email" class="form-control" name="site_email" 
                                                       value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Contact Phone</label>
                                                <input type="text" class="form-control" name="site_phone" 
                                                       value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Business Address</label>
                                                <input type="text" class="form-control" name="site_address" 
                                                       value="<?= htmlspecialchars($settings['site_address'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Facebook URL</label>
                                                <input type="url" class="form-control" name="facebook_url" 
                                                       value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Instagram URL</label>
                                                <input type="url" class="form-control" name="instagram_url" 
                                                       value="<?= htmlspecialchars($settings['instagram_url'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Twitter URL</label>
                                                <input type="url" class="form-control" name="twitter_url" 
                                                       value="<?= htmlspecialchars($settings['twitter_url'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">About Us Text</label>
                                        <textarea class="form-control" name="about_text" rows="4"><?= htmlspecialchars($settings['about_text'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Delivery Information</label>
                                        <textarea class="form-control" name="delivery_info" rows="3"><?= htmlspecialchars($settings['delivery_info'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Privacy Policy</label>
                                                <textarea class="form-control" name="privacy_policy" rows="6"><?= htmlspecialchars($settings['privacy_policy'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Terms & Conditions</label>
                                                <textarea class="form-control" name="terms_conditions" rows="6"><?= htmlspecialchars($settings['terms_conditions'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>
                                        Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Banners Tab -->
                    <div class="tab-pane fade" id="banners" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Banner Management</h5>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bannerModal">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Add New Banner
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (empty($banners)): ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-image display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No banners found.</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bannerModal">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            Add Your First Banner
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($banners as $banner): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <img src="<?= $banner['image_url'] ?>" class="card-img-top" alt="<?= $banner['title'] ?>" style="height: 200px; object-fit: cover;">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= htmlspecialchars($banner['title']) ?></h6>
                                                    <p class="card-text small text-muted"><?= htmlspecialchars($banner['description']) ?></p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-<?= $banner['is_active'] ? 'success' : 'secondary' ?>">
                                                            <?= $banner['is_active'] ? 'Active' : 'Inactive' ?>
                                                        </span>
                                                        <div class="btn-group btn-group-sm">
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="update_banner_status">
                                                                <input type="hidden" name="banner_id" value="<?= $banner['id'] ?>">
                                                                <input type="hidden" name="is_active" value="<?= $banner['is_active'] ? 0 : 1 ?>">
                                                                <button type="submit" class="btn btn-<?= $banner['is_active'] ? 'warning' : 'success' ?> btn-sm">
                                                                    <i class="bi bi-<?= $banner['is_active'] ? 'pause' : 'play' ?>"></i>
                                                                </button>
                                                            </form>
                                                            <button class="btn btn-danger btn-sm">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
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

                    <!-- Pages Tab -->
                    <div class="tab-pane fade" id="pages" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Page Content Editor</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($pages as $page_key => $page_name): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= $page_name ?></h6>
                                                <p class="card-text text-muted small">Edit content and SEO for the <?= strtolower($page_name) ?></p>
                                                <a href="edit_page.php?page=<?= $page_key ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil me-1"></i>
                                                    Edit Page
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Tab -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">SEO & Meta Settings</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_seo_settings">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Meta Title</label>
                                                <input type="text" class="form-control" name="meta_title" 
                                                       value="<?= htmlspecialchars($settings['meta_title'] ?? '') ?>" 
                                                       placeholder="Default page title">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Meta Description</label>
                                                <textarea class="form-control" name="meta_description" rows="2" 
                                                          placeholder="Default page description"><?= htmlspecialchars($settings['meta_description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Keywords</label>
                                        <input type="text" class="form-control" name="meta_keywords" 
                                               value="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>" 
                                               placeholder="keyword1, keyword2, keyword3">
                                        <div class="form-text">Separate keywords with commas</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Google Analytics Code</label>
                                        <textarea class="form-control" name="google_analytics" rows="4" 
                                                  placeholder="Paste your Google Analytics tracking code here"><?= htmlspecialchars($settings['google_analytics'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Facebook Pixel Code</label>
                                        <textarea class="form-control" name="facebook_pixel" rows="4" 
                                                  placeholder="Paste your Facebook Pixel code here"><?= htmlspecialchars($settings['facebook_pixel'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>
                                        Save SEO Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Banner Modal -->
    <div class="modal fade" id="bannerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="upload_banner">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Banner Type</label>
                                    <select class="form-select" name="banner_type" required>
                                        <option value="home">Home Page Banner</option>
                                        <option value="promo">Promotional Banner</option>
                                        <option value="sidebar">Sidebar Banner</option>
                                        <option value="popup">Popup Banner</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" checked>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" required 
                                   placeholder="https://example.com/banner-image.jpg">
                            <div class="form-text">Enter the full URL of the banner image</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required 
                                   placeholder="Banner title">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Banner description or call-to-action"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize TinyMCE for rich text editors
        tinymce.init({
            selector: 'textarea',
            plugins: 'advlist autolink lists link image charmap preview anchor',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
            height: 300,
            menubar: false
        });

        // Tab persistence
        const tabEl = document.querySelector('button[data-bs-toggle="tab"]');
        if (tabEl) {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('activeContentTab', event.target.getAttribute('data-bs-target'));
            });
        }

        // Restore active tab
        const activeTab = localStorage.getItem('activeContentTab');
        if (activeTab) {
            const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
            if (triggerEl) {
                bootstrap.Tab.getOrCreateInstance(triggerEl).show();
            }
        }
    </script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>