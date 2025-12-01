<?php
// Common CSS styles for both admin and owner panels
?>
<style>
:root {
    --owner-primary: #2c3e50;
    --owner-secondary: #34495e;
    --admin-primary: #3498db;
    --admin-secondary: #2980b9;
    --success: #28a745;
    --warning: #ffc107;
    --danger: #dc3545;
    --dark: #343a40;
}

/* Common Card Styles */
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-weight: 500;
    font-size: 0.9rem;
}

/* Badge Styles */
.badge-owner { background: var(--owner-primary); color: white; }
.badge-admin { background: var(--admin-primary); color: white; }
.badge-pending { background: var(--warning); color: black; }
.badge-delivered { background: var(--success); color: white; }
.badge-cancelled { background: var(--danger); color: white; }

/* Button Styles */
.btn-owner {
    background: var(--owner-primary);
    border-color: var(--owner-primary);
    color: white;
}

.btn-owner:hover {
    background: var(--owner-secondary);
    border-color: var(--owner-secondary);
    color: white;
}

.btn-admin {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: white;
}

.btn-admin:hover {
    background: var(--admin-secondary);
    border-color: var(--admin-secondary);
    color: white;
}

/* Table Styles */
.table th {
    border-top: none;
    font-weight: 600;
    color: var(--owner-primary);
    background: #f8f9fa;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

/* Form Styles */
.form-control:focus {
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

/* Navigation Enhancements */
.navbar-brand {
    font-weight: 700;
    font-size: 1.3rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stat-number {
        font-size: 2rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

/* Loading Animation */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--admin-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>