/**
 * Technician Role JavaScript
 * Custom JS for technician-specific functionality
 */

// Immediate console log to verify script is loading
console.log('🚀 Technician.js: Script loaded successfully');
console.log('📅 Script version: Updated with proper username generation (firstName[0] + lastName)');

// Form validation utility
const TechnicianFormValidation = {
    init: function() {
        this.setupFormValidation();
    },

    setupFormValidation: function() {
        window.addEventListener('load', function() {
            const forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    }
};

// Patient form utilities
const PatientFormUtils = {
    init: function() {
        this.setupUsernameGeneration();
    },

    setupUsernameGeneration: function() {
        // Support multiple possible field name patterns (CakePHP vs standard HTML)
        let firstNameInput = document.querySelector('input[name="first_name"]') || 
                            document.querySelector('input[name="Users[first_name]"]') ||
                            document.querySelector('input[id*="first-name"]') ||
                            document.querySelector('input[id*="first_name"]');
                            
        let lastNameInput = document.querySelector('input[name="last_name"]') || 
                           document.querySelector('input[name="Users[last_name]"]') ||
                           document.querySelector('input[id*="last-name"]') ||
                           document.querySelector('input[id*="last_name"]');
                           
        let usernameInput = document.querySelector('input[name="username"]') || 
                           document.querySelector('input[name="Users[username]"]') ||
                           document.querySelector('input[id*="username"]');
        
        // Debug logging to help troubleshoot
        console.log('Username generation debug:');
        console.log('- First name input:', firstNameInput);
        console.log('- Last name input:', lastNameInput);
        console.log('- Username input:', usernameInput);
        
        if (!firstNameInput || !lastNameInput || !usernameInput) {
            console.log('Username generation: Required fields not found');
            // Log available form inputs to help debug
            const allInputs = document.querySelectorAll('input[type="text"], input[type="email"]');
            console.log('Available form inputs:', Array.from(allInputs).map(inp => ({
                name: inp.name,
                id: inp.id,
                placeholder: inp.placeholder
            })));
            return; // Elements not found, probably not on patient form
        }
        
        console.log('Username generation: Initialized successfully');
        
        function generateUsername() {
            console.log('🔍 generateUsername function called');
            
            // Only generate if username field is empty
            if (!usernameInput.value.trim() && firstNameInput.value && lastNameInput.value) {
                console.log('✅ Conditions met for username generation');
                
                const firstName = firstNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
                const lastName = lastNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
                
                console.log('📝 Names processed:', { 
                    originalFirst: firstNameInput.value, 
                    processedFirst: firstName,
                    originalLast: lastNameInput.value,
                    processedLast: lastName,
                    firstNameLength: firstName.length,
                    lastNameLength: lastName.length
                });
                
                // Require minimum length for both names to avoid premature generation
                if (firstName && lastName && firstName.length >= 1 && lastName.length >= 2) {
                    // Generate username: first letter of first name + full last name (proper format)
                    const baseUsername = firstName.charAt(0) + lastName;
                    
                    console.log('🎯 Username generation debug:', {
                        firstName: firstName,
                        lastName: lastName,
                        baseUsername: baseUsername,
                        format: 'first_initial + full_last_name',
                        expected: `${firstName.charAt(0)} + ${lastName} = ${baseUsername}`
                    });
                    
                    // Check if username is unique
                    PatientFormUtils.checkUsernameUniqueness(baseUsername, function(isUnique, suggestedUsername) {
                        const finalUsername = suggestedUsername || baseUsername;
                        
                        // Fill in the username field
                        usernameInput.value = finalUsername;
                        
                        // Add visual feedback
                        if (isUnique) {
                            usernameInput.style.backgroundColor = '#e8f5e8'; // Green for unique
                        } else {
                            usernameInput.style.backgroundColor = '#fff3cd'; // Yellow for modified
                        }
                        
                        setTimeout(() => {
                            usernameInput.style.backgroundColor = '';
                        }, 2000);
                        
                        // Update placeholder
                        usernameInput.placeholder = isUnique ? 
                            `Generated: ${finalUsername}` : 
                            `Modified for uniqueness: ${finalUsername}`;
                        
                        console.log('Username generated:', finalUsername, isUnique ? '(unique)' : '(modified for uniqueness)');
                        
                        // Trigger change event
                        usernameInput.dispatchEvent(new Event('change', { bubbles: true }));
                        usernameInput.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                } else {
                    console.log('⚠️ Username generation skipped: minimum length not met', {
                        firstNameLength: firstName.length,
                        lastNameLength: lastName.length,
                        minimumRequired: 'firstName >= 1, lastName >= 2'
                    });
                }
            } else {
                console.log('⏭️ Username generation skipped: conditions not met', {
                    usernameEmpty: !usernameInput.value.trim(),
                    hasFirstName: !!firstNameInput.value,
                    hasLastName: !!lastNameInput.value
                });
            }
        }
        
        // Debounced version of generateUsername to avoid excessive calls
        let usernameGenerationTimeout;
        function debouncedGenerateUsername() {
            clearTimeout(usernameGenerationTimeout);
            usernameGenerationTimeout = setTimeout(generateUsername, 300); // Wait 300ms after user stops typing
        }
        
        // Set up event listeners with debouncing
        firstNameInput.addEventListener('input', debouncedGenerateUsername);
        lastNameInput.addEventListener('input', debouncedGenerateUsername);
    },

    /**
     * Check username uniqueness via AJAX
     */
    checkUsernameUniqueness: function(username, callback) {
        // Get CSRF token from the page
        let csrfToken = '';
        
        // Try to get CSRF token from meta tag
        const metaCsrf = document.querySelector('meta[name="csrfToken"]');
        if (metaCsrf) {
            csrfToken = metaCsrf.getAttribute('content');
        } else {
            // Try to get from cookie
            const cookieMatch = document.cookie.match(/csrfToken=([^;]+)/);
            if (cookieMatch) {
                csrfToken = cookieMatch[1];
            }
        }
        
        // Prepare headers
        const headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        };
        
        // Add CSRF token to headers
        if (csrfToken) {
            headers['X-CSRF-Token'] = csrfToken;
        }
        
        // Prepare form data instead of JSON
        const formData = new URLSearchParams();
        formData.append('username', username);
        if (csrfToken) {
            formData.append('_csrfToken', csrfToken);
        }
        
        // Make AJAX request to check if username exists
        fetch('/technician/patients/check-username', {
            method: 'POST',
            headers: headers,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                callback(true, username);
            } else {
                // Username exists, use server suggestion or generate alternative
                const suggestion = data.suggestion || PatientFormUtils.generateAlternativeUsername(username);
                callback(false, suggestion);
            }
        })
        .catch(error => {
            console.log('Username check failed, using base username:', error);
            // Fallback: use original username if check fails
            callback(true, username);
        });
    },

    /**
     * Generate alternative username if original is taken
     */
    generateAlternativeUsername: function(baseUsername) {
        const timestamp = Date.now().toString().slice(-3); // Last 3 digits of timestamp
        return baseUsername + timestamp;
    }
};

// General technician utilities
const TechnicianUtils = {
    init: function() {
        this.setupTooltips();
        this.setupConfirmations();
    },

    setupTooltips: function() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    },

    setupConfirmations: function() {
        // Add confirmation dialogs for delete actions
        document.addEventListener('click', function(e) {
            if (e.target.matches('.btn-danger[data-confirm]') || 
                e.target.closest('.btn-danger[data-confirm]')) {
                const button = e.target.matches('.btn-danger[data-confirm]') ? 
                            e.target : e.target.closest('.btn-danger[data-confirm]');
                const message = button.getAttribute('data-confirm');
                if (message && !confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
};

// Initialize all technician modules when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Technician JS: DOM Content Loaded');
    TechnicianFormValidation.init();
    PatientFormUtils.init();
    TechnicianUtils.init();
});

// Also try immediate initialization in case DOM is already loaded
if (document.readyState === 'loading') {
    // DOM hasn't finished loading yet
    console.log('Technician JS: DOM still loading, waiting for DOMContentLoaded');
} else {
    // DOM has already loaded
    console.log('Technician JS: DOM already loaded, initializing immediately');
    TechnicianFormValidation.init();
    PatientFormUtils.init();
    TechnicianUtils.init();
}

// Additional fallback for window load event
window.addEventListener('load', function() {
    console.log('Technician JS: Window load event triggered');
    // Re-initialize in case DOM events didn't work
    PatientFormUtils.init();
});

// Export for potential use in other scripts
window.TechnicianJS = {
    FormValidation: TechnicianFormValidation,
    PatientFormUtils: PatientFormUtils,
    Utils: TechnicianUtils,
    
    // Helper function to manually generate username (useful for debugging)
    generateUsernameNow: function() {
        // Support multiple possible field name patterns
        const firstNameInput = document.querySelector('input[name="first_name"]') || 
                              document.querySelector('input[name="Users[first_name]"]') ||
                              document.querySelector('input[id*="first-name"]') ||
                              document.querySelector('input[id*="first_name"]');
                              
        const lastNameInput = document.querySelector('input[name="last_name"]') || 
                             document.querySelector('input[name="Users[last_name]"]') ||
                             document.querySelector('input[id*="last-name"]') ||
                             document.querySelector('input[id*="last_name"]');
                             
        const usernameInput = document.querySelector('input[name="username"]') || 
                             document.querySelector('input[name="Users[username]"]') ||
                             document.querySelector('input[id*="username"]');
        
        console.log('Manual generation - Found fields:', {
            firstName: firstNameInput,
            lastName: lastNameInput,
            username: usernameInput
        });
        
        if (firstNameInput && lastNameInput && usernameInput) {
            const firstName = firstNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
            const lastName = lastNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
            
            if (firstName && lastName) {
                const baseUsername = firstName.charAt(0) + lastName;
                
                // Use the same function as in setupUsernameGeneration
                PatientFormUtils.checkUsernameUniqueness(baseUsername, function(isUnique, suggestedUsername) {
                    const finalUsername = suggestedUsername || baseUsername;
                    usernameInput.value = finalUsername;
                    
                    // Add visual feedback
                    usernameInput.style.backgroundColor = isUnique ? '#e8f5e8' : '#fff3cd';
                    setTimeout(() => {
                        usernameInput.style.backgroundColor = '';
                    }, 2000);
                    
                    // Trigger events
                    usernameInput.dispatchEvent(new Event('change', { bubbles: true }));
                    usernameInput.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    console.log('Manual username generation:', finalUsername, isUnique ? '(unique)' : '(modified)');
                    return finalUsername;
                });
            } else {
                console.log('Manual username generation: Missing first or last name values');
                console.log('First name value:', firstNameInput.value);
                console.log('Last name value:', lastNameInput.value);
                return null;
            }
        } else {
            console.log('Manual username generation: Required fields not found');
            return null;
        }
    }
};