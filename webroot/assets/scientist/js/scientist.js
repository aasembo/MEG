/**
 * Scientist Role JavaScript
 * Research-focused functionality and utilities
 */

// Document ready initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Scientist dashboard initialized');
    
    // Initialize research dashboard components
    initializeResearchComponents();
    initializeDataValidation();
    initializeChartComponents();
});

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