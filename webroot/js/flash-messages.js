/**
 * Enhanced Flash Messages JavaScript
 * Provides auto-dismiss, smooth animations, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Flash Message Manager
     */
    const FlashManager = {
        
        // Configuration
        config: {
            autoDismissDelay: 5000,
            animationDuration: 300,
            pauseOnHover: true,
            stackLimit: 5
        },
        
        // Initialize flash message functionality
        init: function() {
            this.setupAutoDismiss();
            this.setupCloseHandlers();
            this.setupKeyboardHandlers();
            this.limitMessageStack();
        },
        
        // Setup auto-dismiss functionality
        setupAutoDismiss: function() {
            const autoDismissAlerts = document.querySelectorAll('.flash-auto-dismiss');
            
            autoDismissAlerts.forEach((alert, index) => {
                // Stagger multiple messages
                const delay = this.config.autoDismissDelay + (index * 100);
                
                const dismissTimer = setTimeout(() => {
                    this.dismissAlert(alert);
                }, delay);
                
                // Pause on hover if enabled
                if (this.config.pauseOnHover) {
                    let remainingTime = delay;
                    let startTime = Date.now();
                    
                    alert.addEventListener('mouseenter', () => {
                        clearTimeout(dismissTimer);
                        remainingTime -= (Date.now() - startTime);
                    });
                    
                    alert.addEventListener('mouseleave', () => {
                        if (remainingTime > 0) {
                            startTime = Date.now();
                            setTimeout(() => {
                                this.dismissAlert(alert);
                            }, remainingTime);
                        }
                    });
                }
                
                // Store timer reference for manual clearing
                alert.dataset.dismissTimer = dismissTimer;
            });
        },
        
        // Setup close button handlers
        setupCloseHandlers: function() {
            const closeButtons = document.querySelectorAll('.alert .btn-close');
            
            closeButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const alert = button.closest('.alert');
                    this.dismissAlert(alert);
                });
            });
        },
        
        // Setup keyboard handlers for accessibility
        setupKeyboardHandlers: function() {
            document.addEventListener('keydown', (e) => {
                // ESC key dismisses focused alert
                if (e.key === 'Escape') {
                    const focusedAlert = document.activeElement.closest('.alert');
                    if (focusedAlert) {
                        this.dismissAlert(focusedAlert);
                    }
                }
                
                // Ctrl+Shift+X dismisses all alerts
                if (e.ctrlKey && e.shiftKey && e.key === 'X') {
                    this.dismissAllAlerts();
                }
            });
        },
        
        // Limit number of messages displayed
        limitMessageStack: function() {
            const container = document.querySelector('.flash-messages');
            if (!container) return;
            
            const alerts = container.querySelectorAll('.alert');
            if (alerts.length > this.config.stackLimit) {
                // Remove oldest alerts
                for (let i = 0; i < alerts.length - this.config.stackLimit; i++) {
                    this.dismissAlert(alerts[i], false); // No animation for cleanup
                }
            }
        },
        
        // Dismiss a single alert
        dismissAlert: function(alert, animate = true) {
            if (!alert || alert.classList.contains('flash-dismissing')) return;
            
            alert.classList.add('flash-dismissing');
            
            // Clear any auto-dismiss timer
            if (alert.dataset.dismissTimer) {
                clearTimeout(parseInt(alert.dataset.dismissTimer));
            }
            
            if (animate) {
                alert.classList.add('flash-fade-out');
                setTimeout(() => {
                    this.removeAlert(alert);
                }, this.config.animationDuration);
            } else {
                this.removeAlert(alert);
            }
        },
        
        // Remove alert from DOM
        removeAlert: function(alert) {
            if (alert && alert.parentNode) {
                // Trigger custom event
                const event = new CustomEvent('flashDismissed', {
                    detail: { alert: alert }
                });
                document.dispatchEvent(event);
                
                alert.remove();
            }
        },
        
        // Dismiss all alerts
        dismissAllAlerts: function() {
            const alerts = document.querySelectorAll('.alert:not(.flash-dismissing)');
            alerts.forEach((alert, index) => {
                setTimeout(() => {
                    this.dismissAlert(alert);
                }, index * 50); // Stagger dismissal
            });
        },
        
        // Create and show new flash message
        showMessage: function(message, type = 'primary', options = {}) {
            const container = this.getOrCreateContainer();
            
            const alertConfig = {
                type: type,
                icon: options.icon || this.getDefaultIcon(type),
                dismissible: options.dismissible !== false,
                autoDismiss: options.autoDismiss !== false,
                ...options
            };
            
            const alertHtml = this.buildAlertHtml(message, alertConfig);
            
            // Insert new alert
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Initialize new alert
            const newAlert = container.firstElementChild;
            this.initializeAlert(newAlert);
            
            return newAlert;
        },
        
        // Get or create messages container
        getOrCreateContainer: function() {
            let container = document.querySelector('.flash-messages');
            if (!container) {
                container = document.createElement('div');
                container.className = 'flash-messages';
                
                // Insert after main navigation or at top of content
                const nav = document.querySelector('nav.navbar');
                const main = document.querySelector('main, .content');
                const target = nav ? nav.nextElementSibling : (main || document.body.firstElementChild);
                
                if (target) {
                    target.parentNode.insertBefore(container, target);
                } else {
                    document.body.prepend(container);
                }
            }
            return container;
        },
        
        // Build alert HTML
        buildAlertHtml: function(message, config) {
            const classes = ['alert', 'alert-' + config.type];
            if (config.dismissible) classes.push('alert-dismissible');
            if (config.autoDismiss) classes.push('flash-auto-dismiss');
            if (config.class) classes.push(config.class);
            
            return '<div class="' + classes.join(' ') + '" role="alert">' +
                '<div class="d-flex align-items-start">' +
                '<i class="' + config.icon + ' flash-icon me-2 mt-1"></i>' +
                '<div class="flex-grow-1">' + this.escapeHtml(message) + '</div>' +
                (config.dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '') +
                '</div>' +
                '</div>';
        },
        
        // Initialize a new alert
        initializeAlert: function(alert) {
            // Setup close handler
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.dismissAlert(alert);
                });
            }
            
            // Setup auto-dismiss
            if (alert.classList.contains('flash-auto-dismiss')) {
                const timer = setTimeout(() => {
                    this.dismissAlert(alert);
                }, this.config.autoDismissDelay);
                
                alert.dataset.dismissTimer = timer;
                
                // Pause on hover
                if (this.config.pauseOnHover) {
                    let remainingTime = this.config.autoDismissDelay;
                    let startTime = Date.now();
                    
                    alert.addEventListener('mouseenter', () => {
                        clearTimeout(timer);
                        remainingTime -= (Date.now() - startTime);
                    });
                    
                    alert.addEventListener('mouseleave', () => {
                        if (remainingTime > 0) {
                            startTime = Date.now();
                            setTimeout(() => {
                                this.dismissAlert(alert);
                            }, remainingTime);
                        }
                    });
                }
            }
        },
        
        // Get default icon for message type
        getDefaultIcon: function(type) {
            const icons = {
                success: 'fas fa-check-circle',
                danger: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle',
                primary: 'fas fa-info-circle'
            };
            return icons[type] || icons.primary;
        },
        
        // Escape HTML
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize Flash Manager
    FlashManager.init();
    
    // Expose FlashManager globally for programmatic use
    window.FlashManager = FlashManager;
    
    // Convenience methods
    window.showFlash = function(message, type, options) {
        return FlashManager.showMessage(message, type, options);
    };
    
    window.showSuccess = function(message, options) {
        return FlashManager.showMessage(message, 'success', options);
    };
    
    window.showError = function(message, options) {
        return FlashManager.showMessage(message, 'danger', options);
    };
    
    window.showWarning = function(message, options) {
        return FlashManager.showMessage(message, 'warning', options);
    };
    
    window.showInfo = function(message, options) {
        return FlashManager.showMessage(message, 'info', options);
    };
    
    // AJAX integration
    document.addEventListener('ajaxSuccess', function(e) {
        if (e.detail && e.detail.flash) {
            const flash = e.detail.flash;
            FlashManager.showMessage(flash.message, flash.type, flash.options);
        }
    });
    
    // Handle form submissions with flash messages
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.dataset.flashOnSubmit) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.flash) {
                    FlashManager.showMessage(data.flash.message, data.flash.type);
                }
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(error => {
                FlashManager.showMessage('An error occurred. Please try again.', 'danger');
            });
        }
    });
});