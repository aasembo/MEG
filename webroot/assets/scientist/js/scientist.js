/**
 * Scientist Role JavaScript
 * Research-focused functionality and utilities
 * Version: 1.1.0 - Fixed initializeDataValidation function
 */

/**
 * Initialize data validation components
 */
function initializeDataValidation() {
    console.log('Initializing data validation...');
    
    // Initialize form validation for research data
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        // Add real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
    });
    
    // Initialize data integrity checks
    initializeDataIntegrityChecks();
}

// Document ready initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Scientist dashboard initialized - v1.1.0');
    
    // Initialize research dashboard components
    initializeResearchComponents();
    initializeDataValidation();
    initializeChartComponents();
});

/**
 * Validate individual form field
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.dataset.validateType || field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Check if field is required
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    // Validate specific field types
    else if (value) {
        switch (fieldType) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
                break;
                
            case 'number':
                if (isNaN(value) || isNaN(parseFloat(value))) {
                    isValid = false;
                    errorMessage = 'Please enter a valid number';
                }
                break;
                
            case 'sample-id':
                const sampleRegex = /^[A-Z0-9-]+$/;
                if (!sampleRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Sample ID must contain only letters, numbers, and hyphens';
                }
                break;
                
            case 'date':
                const date = new Date(value);
                if (isNaN(date.getTime())) {
                    isValid = false;
                    errorMessage = 'Please enter a valid date';
                }
                break;
        }
    }
    
    // Apply validation result
    if (isValid) {
        clearFieldError(field);
    } else {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Initialize data integrity checks
 */
function initializeDataIntegrityChecks() {
    // Check for duplicate sample IDs
    const sampleIdInputs = document.querySelectorAll('input[data-validate-type="sample-id"]');
    
    sampleIdInputs.forEach(input => {
        input.addEventListener('blur', function() {
            checkSampleIdUniqueness(this);
        });
    });
    
    // Initialize batch validation for research data
    const batchValidationTriggers = document.querySelectorAll('[data-batch-validate]');
    batchValidationTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const target = this.dataset.batchValidate;
            performBatchValidation(target);
        });
    });
}

/**
 * Check if sample ID is unique
 */
function checkSampleIdUniqueness(input) {
    const sampleId = input.value.trim();
    
    if (!sampleId) return;
    
    // This would typically make an AJAX call to check uniqueness
    // For now, simulate the check
    console.log('Checking uniqueness for sample ID:', sampleId);
    
    // Simulate API call
    setTimeout(() => {
        // For demo purposes, mark as invalid if it contains "duplicate"
        if (sampleId.toLowerCase().includes('duplicate')) {
            showFieldError(input, 'This sample ID already exists');
        } else {
            clearFieldError(input);
        }
    }, 500);
}

/**
 * Perform batch validation on a set of data
 */
function performBatchValidation(target) {
    console.log('Performing batch validation for:', target);
    
    const container = document.querySelector(target);
    if (!container) return;
    
    const forms = container.querySelectorAll('form');
    let allValid = true;
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (!validateField(input)) {
                allValid = false;
            }
        });
    });
    
    // Show batch validation result
    const message = allValid ? 
        'All data passed validation checks' : 
        'Some fields require attention';
    const type = allValid ? 'success' : 'warning';
    
    showMessage(message, type);
}

/**
 * Initialize research-specific components
 */
function initializeResearchComponents() {
    // Sample tracking functionality
    initializeSampleTracking();
    
    // Research forms
    initializeResearchForms();
    
    // Data export functionality
    initializeDataExport();
}

/**
 * Sample tracking system
 */
function initializeSampleTracking() {
    const sampleCards = document.querySelectorAll('.sample-card');
    
    sampleCards.forEach(card => {
        card.addEventListener('click', function() {
            const sampleId = this.dataset.sampleId;
            if (sampleId) {
                showSampleDetails(sampleId);
            }
        });
    });
}

/**
 * Show sample details modal
 */
function showSampleDetails(sampleId) {
    // Implementation for sample details display
    console.log('Showing details for sample:', sampleId);
    
    // This would typically open a modal or navigate to details page
    // For now, just log the action
}

/**
 * Initialize research forms with validation
 */
function initializeResearchForms() {
    const forms = document.querySelectorAll('.research-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateResearchForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Validate research forms
 */
function validateResearchForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    return isValid;
}

/**
 * Show field validation error
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field validation error
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Initialize data export functionality
 */
function initializeDataExport() {
    const exportButtons = document.querySelectorAll('.export-data');
    
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const format = this.dataset.format || 'csv';
            const dataType = this.dataset.type || 'general';
            
            exportData(dataType, format);
        });
    });
}

/**
 * Export research data
 */
function exportData(dataType, format) {
    console.log(`Exporting ${dataType} data in ${format} format`);
    
    // This would typically make an AJAX call to export endpoint
    // For now, just show a message
    showMessage(`Exporting ${dataType} data as ${format.toUpperCase()}...`, 'info');
}

/**
 * Initialize chart components
 */
function initializeChartComponents() {
    // Basic chart initialization
    const chartContainers = document.querySelectorAll('.chart-container');
    
    chartContainers.forEach(container => {
        const chartType = container.dataset.chartType;
        if (chartType) {
            initializeChart(container, chartType);
        }
    });
}

/**
 * Initialize individual chart
 */
function initializeChart(container, type) {
    console.log(`Initializing ${type} chart in container:`, container);
    
    // This would integrate with Chart.js or other charting library
    // For now, just add a placeholder
    container.innerHTML = `<p class="text-muted text-center p-4">Chart placeholder - ${type}</p>`;
}

/**
 * Show message to user
 */
function showMessage(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.content') || document.body;
    mainContent.insertBefore(alert, mainContent.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

/**
 * Research data utilities
 */
const ResearchUtils = {
    /**
     * Format sample ID for display
     */
    formatSampleId: function(id) {
        return `SAMPLE-${String(id).padStart(6, '0')}`;
    },
    
    /**
     * Format date for research logs
     */
    formatResearchDate: function(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    /**
     * Calculate research progress
     */
    calculateProgress: function(completed, total) {
        if (total === 0) return 0;
        return Math.round((completed / total) * 100);
    },
    
    /**
     * Generate research report ID
     */
    generateReportId: function() {
        const now = new Date();
        const timestamp = now.getTime().toString().slice(-6);
        return `RPT-${timestamp}`;
    }
};

// Make utilities available globally
window.ResearchUtils = ResearchUtils;