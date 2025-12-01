<?php
// admin/styles.php - Consistent color scheme matching user pages
?>
<style>
:root {
    /* Match user page color scheme exactly */
    --primary-50: #FFF6E9;
    --primary-100: #5F372B;
    --primary-200: #4A2B22;
    --primary-300: #5F372B;
    --primary-400: #4A2B22;
    --primary-500: #5F372B;
    --primary-600: #4A2B22;
    --primary-700: #5F372B;
    --primary-800: #4A2B22;
    --primary-900: #3A231F;
    
    --neutral-100: #FFF6E9;
    --neutral-200: #F5E6D6;
    --neutral-300: #E8D9C8;
    --text-dark: #5F372B;
    --text-muted: #6B6B6B;
    --gold-accent: #D4A373;
}

body {
    background: var(--primary-50);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--text-dark);
    min-height: 100vh;
}

/* Navigation */
.navbar {
    background: var(--primary-100) !important;
    border-bottom: 1px solid var(--primary-200);
}

.navbar-brand, .nav-link {
    color: white !important;
    font-weight: 500;
}

.nav-link:hover {
    color: var(--primary-50) !important;
    background: rgba(255, 246, 233, 0.1);
}

/* Cards and Containers */
.welcome-section, .stat-card, .card, .filters-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(95, 55, 43, 0.1);
    border: 1px solid var(--neutral-200);
    transition: all 0.3s ease;
}

.stat-card {
    padding: 1.5rem;
    text-align: center;
}

.card-header {
    background: white !important;
    border-bottom: 1px solid var(--neutral-200);
    color: var(--text-dark) !important;
    font-weight: 600;
}

/* Tables */
.table th {
    background: var(--primary-100) !important;
    color: white !important;
    border: none;
    font-weight: 600;
}

.table-hover tbody tr:hover {
    background-color: rgba(95, 55, 43, 0.05);
}

/* Buttons */
.btn-primary {
    background: var(--primary-100);
    border: none;
    color: white;
    font-weight: 500;
}

.btn-primary:hover {
    background: var(--primary-200);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(95, 55, 43, 0.2);
}

.btn-outline-primary {
    border-color: var(--primary-100);
    color: var(--primary-100);
}

.btn-outline-primary:hover {
    background: var(--primary-100);
    color: white;
}

/* Status Badges */
.badge-pending { 
    background: rgba(255, 193, 7, 0.2); 
    color: #856404; 
    border: 1px solid rgba(255, 193, 7, 0.3);
}
.badge-confirmed { 
    background: rgba(23, 162, 184, 0.2); 
    color: #0c5460; 
    border: 1px solid rgba(23, 162, 184, 0.3);
}
.badge-preparing { 
    background: rgba(253, 126, 20, 0.2); 
    color: #854d0e; 
    border: 1px solid rgba(253, 126, 20, 0.3);
}
.badge-delivered { 
    background: rgba(40, 167, 69, 0.2); 
    color: #155724; 
    border: 1px solid rgba(40, 167, 69, 0.3);
}
.badge-cancelled { 
    background: rgba(220, 53, 69, 0.2); 
    color: #721c24; 
    border: 1px solid rgba(220, 53, 69, 0.3);
}

/* Stats and Numbers */
.stat-number {
    color: var(--primary-100);
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
}

/* Form Elements */
.form-control, .form-select {
    border: 1px solid var(--neutral-300);
    background: white;
    color: var(--text-dark);
    border-radius: 8px;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-100);
    box-shadow: 0 0 0 0.2rem rgba(95, 55, 43, 0.1);
}

/* Hover Effects */
.stat-card:hover, .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(95, 55, 43, 0.15);
}

/* Modal Improvements */
.modal-header {
    background: var(--primary-100);
    color: white;
    border-bottom: 1px solid var(--primary-200);
}

.modal-header .btn-close {
    filter: invert(1);
}

/* Role Badges */
.badge-super-admin {
    background: linear-gradient(135deg, #E53E3E 0%, #C53030 100%);
    color: white;
}

.badge-admin {
    background: var(--primary-100);
    color: white;
}

.badge-moderator {
    background: linear-gradient(135deg, #4299E1 0%, #3182CE 100%);
    color: white;
}

/* Alerts */
.alert {
    border: none;
    border-radius: 8px;
    border-left: 4px solid;
}

.alert-success {
    background: rgba(95, 55, 43, 0.1);
    border-left-color: var(--primary-100);
    color: var(--text-dark);
}

.alert-danger {
    background: rgba(74, 43, 34, 0.1);
    border-left-color: var(--primary-200);
    color: var(--text-dark);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-muted);
}

/* Welcome Section */
.welcome-section {
    padding: 2rem;
    margin: 2rem 0;
}

/* Filters Card */
.filters-card {
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
</style>