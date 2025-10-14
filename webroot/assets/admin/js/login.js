/**
 * Admin Login Page JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Focus on email field when page loads
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.focus();
    }

    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show validation errors
                const firstInvalidField = form.querySelector(':invalid');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
            } else {
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
                }
            }
            
            form.classList.add('was-validated');
        });
    });

    // Show/hide password functionality
    const passwordField = document.getElementById('password');
    if (passwordField) {
        // Create password toggle button
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2';
        toggleButton.style.zIndex = '10';
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
        toggleButton.setAttribute('aria-label', 'Toggle password visibility');
        
        // Position the password field container relatively
        const passwordContainer = passwordField.closest('.input');
        if (passwordContainer) {
            passwordContainer.style.position = 'relative';
            passwordContainer.appendChild(toggleButton);
            
            // Add padding to prevent text overlap with button
            passwordField.style.paddingRight = '50px';
            
            // Toggle password visibility
            toggleButton.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                    this.setAttribute('aria-label', 'Hide password');
                } else {
                    passwordField.type = 'password';
                    icon.className = 'fas fa-eye';
                    this.setAttribute('aria-label', 'Show password');
                }
            });
        }
    }

    // Remember me functionality (if needed in the future)
    const rememberCheckbox = document.getElementById('remember-me');
    if (rememberCheckbox) {
        // Load saved email if remember me was checked
        const savedEmail = localStorage.getItem('admin_email');
        if (savedEmail && emailField) {
            emailField.value = savedEmail;
            rememberCheckbox.checked = true;
        }
        
        // Save email when form is submitted
        const loginForm = document.querySelector('.needs-validation');
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                if (rememberCheckbox.checked && emailField) {
                    localStorage.setItem('admin_email', emailField.value);
                } else {
                    localStorage.removeItem('admin_email');
                }
            });
        }
    }

    // Auto-focus on password field when email is entered
    if (emailField && passwordField) {
        emailField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                passwordField.focus();
            }
        });
    }

    // Caps Lock detection
    function checkCapsLock(event) {
        const capsLockOn = event.getModifierState('CapsLock');
        const capsWarning = document.getElementById('caps-lock-warning');
        
        if (capsLockOn) {
            if (!capsWarning) {
                const warning = document.createElement('small');
                warning.id = 'caps-lock-warning';
                warning.className = 'text-warning mt-1 d-block';
                warning.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Caps Lock is on';
                passwordField.parentNode.appendChild(warning);
            }
        } else if (capsWarning) {
            capsWarning.remove();
        }
    }

    if (passwordField) {
        passwordField.addEventListener('keyup', checkCapsLock);
    }

    // Smooth card animations
    const loginCard = document.querySelector('.login-card');
    if (loginCard) {
        loginCard.style.opacity = '0';
        loginCard.style.transform = 'translateY(20px)';
        
        setTimeout(function() {
            loginCard.style.transition = 'all 0.5s ease-out';
            loginCard.style.opacity = '1';
            loginCard.style.transform = 'translateY(0)';
        }, 100);
    }

    // Handle login errors with animation
    const errorAlerts = document.querySelectorAll('.alert-danger');
    errorAlerts.forEach(function(alert) {
        alert.style.animation = 'shake 0.5s ease-in-out';
    });

});

// Shake animation for error states
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);