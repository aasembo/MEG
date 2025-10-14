/**
 * Doctor Role JavaScript
 * Custom JS for doctor-specific functionality
 */

// Form validation utility
const DoctorFormValidation = {
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

// Doctor-specific utilities
const DoctorUtils = {
    init: function() {
        this.setupTooltips();
        this.setupConfirmations();
        this.setupPatientSearch();
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
        // Add confirmation dialogs for critical actions
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
    },

    setupPatientSearch: function() {
        // Patient search functionality
        const searchInput = document.querySelector('#patientSearch');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    DoctorUtils.performPatientSearch(searchInput.value);
                }, 300);
            });
        }
    },

    performPatientSearch: function(query) {
        if (query.length < 2) return;
        
        // Implement patient search logic here
        console.log('Searching for patients:', query);
        // This could make an AJAX call to search patients
    }
};

// Medical form utilities
const MedicalFormUtils = {
    init: function() {
        this.setupDiagnosisAutocomplete();
        this.setupMedicationValidation();
    },

    setupDiagnosisAutocomplete: function() {
        // Setup diagnosis autocomplete if available
        const diagnosisInputs = document.querySelectorAll('.diagnosis-input');
        diagnosisInputs.forEach(function(input) {
            // Add autocomplete functionality for common diagnoses
            input.addEventListener('input', function() {
                // Implement diagnosis suggestions
            });
        });
    },

    setupMedicationValidation: function() {
        // Validate medication dosages and interactions
        const medicationInputs = document.querySelectorAll('.medication-input');
        medicationInputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                // Implement medication validation
            });
        });
    }
};

// Initialize all doctor modules when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    DoctorFormValidation.init();
    DoctorUtils.init();
    MedicalFormUtils.init();
});

// Export for potential use in other scripts
window.DoctorJS = {
    FormValidation: DoctorFormValidation,
    Utils: DoctorUtils,
    MedicalFormUtils: MedicalFormUtils
};