// Admin Panel JavaScript Enhancements
class MarsilaseAdmin {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupAnimations();
        this.setupEventListeners();
        this.setupRealTimeUpdates();
        this.setupMobileMenu();
    }
    
    setupAnimations() {
        // Add staggered animations to table rows
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
            row.classList.add('fade-in');
        });
        
        // Add animations to cards
        const cards = document.querySelectorAll('.glass-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.05}s`;
            card.classList.add('slide-up');
        });
    }
    
    setupEventListeners() {
        // Auto-dismiss alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
        
        // Confirm destructive actions
        const destructiveLinks = document.querySelectorAll('a[onclick*="confirm"]');
        destructiveLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const message = link.getAttribute('onclick').match(/'([^']+)'/)[1];
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
        
        // Form validation enhancement
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', this.validateForm);
        });
    }
    
    setupRealTimeUpdates() {
        // Update dashboard stats every 30 seconds
        if (window.location.pathname.includes('dashboard')) {
            setInterval(() => {
                this.updateDashboardStats();
            }, 30000);
        }
        
        // Real-time order notifications
        this.setupOrderNotifications();
    }
    
    setupMobileMenu() {
        const menuBtn = document.querySelector('.mobile-menu-btn');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuBtn && sidebar) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
    
    validateForm(e) {
        const form = e.target;
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                this.highlightField(field, false);
            } else {
                this.highlightField(field, true);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            this.showToast('Please fill in all required fields', 'error');
        }
    }
    
    highlightField(field, isValid) {
        if (isValid) {
            field.style.borderColor = '#198754';
        } else {
            field.style.borderColor = '#dc3545';
            field.focus();
        }
    }
    
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="bi bi-${this.getToastIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    async updateDashboardStats() {
        try {
            const response = await fetch('?action=stats&ajax=1');
            const data = await response.json();
            
            // Update stats cards
            document.querySelectorAll('.stat-card h2').forEach((element, index) => {
                const values = Object.values(data);
                if (values[index] !== undefined) {
                    this.animateValue(element, parseInt(element.textContent), values[index], 1000);
                }
            });
        } catch (error) {
            console.error('Failed to update stats:', error);
        }
    }
    
    animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            element.textContent = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    setupOrderNotifications() {
        // Check for new orders every minute
        setInterval(async () => {
            try {
                const response = await fetch('?action=check_new_orders&ajax=1');
                const { newOrders } = await response.json();
                
                if (newOrders > 0) {
                    this.showNotification(`You have ${newOrders} new order(s)`, 'orders');
                }
            } catch (error) {
                console.error('Failed to check new orders:', error);
            }
        }, 60000);
    }
    
    showNotification(message, type) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Marsilase Pastry', {
                body: message,
                icon: '/admin/images/logo.png'
            });
        }
        
        // Fallback to browser notification
        this.showToast(message, 'info');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MarsilaseAdmin();
});

// Utility functions
const AdminUtils = {
    // Format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-ET', {
            style: 'currency',
            currency: 'ETB'
        }).format(amount);
    },
    
    // Format date
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Generate random ID
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }
};

// Export for global use
window.MarsilaseAdmin = MarsilaseAdmin;
window.AdminUtils = AdminUtils;