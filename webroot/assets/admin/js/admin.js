/**
 * Admin Panel JavaScript Functions
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-dismiss flash messages after 5 seconds
    const autoDismissAlerts = document.querySelectorAll('.alert.auto-dismiss');
    autoDismissAlerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.parentNode) {
                alert.classList.add('fade-out');
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    });

    // Smooth close animation for flash messages
    const closeButtons = document.querySelectorAll('.alert .btn-close');
    closeButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const alert = this.closest('.alert');
            alert.classList.add('fade-out');
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 300);
        });
    });

    // Sidebar toggle for mobile
    const sidebarToggle = document.querySelector('[data-bs-toggle="sidebar"]');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        });
    }

    // Auto-refresh system status indicators
    function updateSystemStatus() {
        // Simulate status checks (replace with actual API calls)
        const statusIndicators = document.querySelectorAll('.system-status .badge');
        statusIndicators.forEach(function(badge) {
            // Add pulse animation to show activity
            badge.style.animation = 'pulse 0.5s ease-in-out';
            setTimeout(function() {
                badge.style.animation = '';
            }, 500);
        });
    }

    // Update system status every 30 seconds
    setInterval(updateSystemStatus, 30000);

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Confirm dialogs for dangerous actions
    const dangerButtons = document.querySelectorAll('.btn-danger[data-confirm]');
    dangerButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Loading state for forms
    const forms = document.querySelectorAll('form[data-loading]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            }
        });
    });

    // Auto-hide alerts after specified time
    const timedAlerts = document.querySelectorAll('.alert[data-timeout]');
    timedAlerts.forEach(function(alert) {
        const timeout = parseInt(alert.getAttribute('data-timeout')) || 5000;
        setTimeout(function() {
            if (alert.parentNode) {
                alert.classList.add('fade-out');
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        }, timeout);
    });

    // Tooltip initialization
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Popover initialization
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

});

/**
 * Show loading overlay
 */
function showLoading() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loading-overlay';
    loadingOverlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50';
    loadingOverlay.style.zIndex = '9999';
    loadingOverlay.innerHTML = `
        <div class="text-center text-white">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div>Loading...</div>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

/**
 * Show success toast
 */
function showSuccessToast(message) {
    showToast(message, 'success');
}

/**
 * Show error toast
 */
function showErrorToast(message) {
    showToast(message, 'danger');
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toast = new bootstrap.Toast(document.getElementById(toastId));
    toast.show();
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}