/**
 * Reports Editor JavaScript
 * Handles rich text editing, templates, and report functionality
 */

class ReportsEditor {
    constructor() {
        this.editor = document.getElementById('report-editor');
        this.hiddenField = document.getElementById('report_data_hidden');
        this.form = document.getElementById('reportForm');
        this.toolbar = document.getElementById('editor-toolbar');
        
        this.initializeEditor();
        this.initializeToolbar();
        this.initializeTemplates();
        this.initializeAutoSave();
        this.initializeValidation();
        this.initializeKeyboardShortcuts();
    }
    
    initializeEditor() {
        if (!this.editor) return;
        
        // Set up contenteditable editor
        this.editor.addEventListener('input', () => {
            this.updateHiddenField();
        });
        
        this.editor.addEventListener('paste', (e) => {
            // Handle paste events to clean up formatting
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });
        
        this.editor.addEventListener('keydown', (e) => {
            // Handle tab key for indentation
            if (e.key === 'Tab') {
                e.preventDefault();
                document.execCommand('insertText', false, '    ');
            }
        });
        
        // Focus styling
        this.editor.addEventListener('focus', () => {
            this.editor.style.backgroundColor = '#fafafa';
            this.editor.style.border = '2px solid #28a745';
        });
        
        this.editor.addEventListener('blur', () => {
            this.editor.style.backgroundColor = '';
            this.editor.style.border = '';
        });
    }
    
    initializeToolbar() {
        if (!this.toolbar) return;
        
        // Format buttons
        const formatButtons = this.toolbar.querySelectorAll('[data-command]');
        formatButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const command = button.dataset.command;
                const value = button.dataset.value || null;
                
                this.executeCommand(command, value);
                this.editor.focus();
            });
        });
        
        // Template generation
        const generateBtn = document.getElementById('generate-template');
        if (generateBtn) {
            generateBtn.addEventListener('click', () => {
                this.showTemplateModal();
            });
        }
        
        // Preview button
        const previewBtn = document.getElementById('preview-report');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => {
                this.previewReport();
            });
        }
    }
    
    initializeTemplates() {
        const templateButtons = document.querySelectorAll('.template-btn');
        templateButtons.forEach(button => {
            button.addEventListener('click', () => {
                const templateType = button.dataset.template;
                this.loadTemplate(templateType);
            });
        });
    }
    
    initializeAutoSave() {
        let saveTimeout;
        
        if (this.editor) {
            this.editor.addEventListener('input', () => {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    this.autoSave();
                }, 2000); // Auto-save after 2 seconds of inactivity
            });
        }
        
        // Save draft button
        const saveDraftBtn = document.getElementById('save-draft');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', () => {
                this.saveDraft();
            });
        }
        
        // Save and preview
        const savePreviewBtn = document.getElementById('save-preview');
        if (savePreviewBtn) {
            savePreviewBtn.addEventListener('click', () => {
                this.saveAndPreview();
            });
        }
    }
    
    initializeValidation() {
        if (!this.form) return;
        
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                return false;
            }
            
            this.updateHiddenField();
        });
    }
    
    initializeKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 's':
                        e.preventDefault();
                        this.saveDraft();
                        break;
                    case 'b':
                        if (this.editor && this.editor.contains(document.activeElement)) {
                            e.preventDefault();
                            this.executeCommand('bold');
                        }
                        break;
                    case 'i':
                        if (this.editor && this.editor.contains(document.activeElement)) {
                            e.preventDefault();
                            this.executeCommand('italic');
                        }
                        break;
                    case 'u':
                        if (this.editor && this.editor.contains(document.activeElement)) {
                            e.preventDefault();
                            this.executeCommand('underline');
                        }
                        break;
                }
            }
        });
    }
    
    executeCommand(command, value = null) {
        document.execCommand(command, false, value);
        this.updateHiddenField();
    }
    
    updateHiddenField() {
        if (!this.editor || !this.hiddenField) return;
        
        const reportData = {
            content: this.editor.innerHTML,
            metadata: {
                lastModified: new Date().toISOString(),
                wordCount: this.getWordCount(),
                characterCount: this.getCharacterCount(),
                version: '1.0'
            }
        };
        
        this.hiddenField.value = JSON.stringify(reportData);
        this.updateWordCount();
    }
    
    getWordCount() {
        if (!this.editor) return 0;
        const text = this.editor.innerText || '';
        return text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
    }
    
    getCharacterCount() {
        if (!this.editor) return 0;
        return (this.editor.innerText || '').length;
    }
    
    updateWordCount() {
        const wordCountElement = document.getElementById('word-count');
        if (wordCountElement) {
            wordCountElement.textContent = this.getWordCount();
        }
        
        const charCountElement = document.getElementById('char-count');
        if (charCountElement) {
            charCountElement.textContent = this.getCharacterCount();
        }
    }
    
    loadTemplate(templateType) {
        const templates = {
            general: this.getGeneralTemplate(),
            pathology: this.getPathologyTemplate(),
            radiology: this.getRadiologyTemplate(),
            laboratory: this.getLaboratoryTemplate()
        };
        
        const template = templates[templateType];
        if (template) {
            if (this.getWordCount() > 0) {
                if (!confirm('This will replace the current content. Are you sure?')) {
                    return;
                }
            }
            
            this.editor.innerHTML = template;
            this.updateHiddenField();
            this.showNotification('Template loaded successfully!', 'success');
        }
    }
    
    getGeneralTemplate() {
        return `
            <h2>Medical Analysis Report</h2>
            <h3>Patient Information</h3>
            <p><strong>Patient Name:</strong> [Patient Name]</p>
            <p><strong>Case ID:</strong> [Case ID]</p>
            <p><strong>Date of Report:</strong> ${new Date().toLocaleDateString()}</p>
            
            <h3>Clinical Summary</h3>
            <p><strong>Chief Complaint:</strong> [Enter the primary reason for patient presentation and analysis]</p>
            
            <h3>Clinical History</h3>
            <p>[Provide detailed clinical history including present illness, past medical history, medications, and relevant family/social history]</p>
            
            <h3>Scientific Analysis</h3>
            <p>[Document systematic analysis including data interpretation, laboratory findings correlation, and evidence-based assessment]</p>
            
            <h3>Assessment and Findings</h3>
            <p>[Provide comprehensive assessment with primary conclusions, supporting evidence, and confidence levels]</p>
            
            <h3>Recommendations</h3>
            <ol>
                <li>Further investigation needs</li>
                <li>Additional testing requirements</li>
                <li>Methodological improvements</li>
                <li>Clinical correlation suggestions</li>
            </ol>
            
            <h3>Conclusion</h3>
            <p>[Summary of key findings and final recommendations]</p>
        `;
    }
    
    getPathologyTemplate() {
        return `
            <h2>Pathological Analysis Report</h2>
            <h3>Specimen Information</h3>
            <p><strong>Specimen Type:</strong> [Specify specimen type]</p>
            <p><strong>Collection Date:</strong> [Collection date and time]</p>
            <p><strong>Specimen ID:</strong> [Unique specimen identifier]</p>
            
            <h3>Gross Examination</h3>
            <p>[Describe macroscopic findings including size, color, texture, and overall appearance]</p>
            
            <h3>Microscopic Analysis</h3>
            <p>[Detailed histological examination including cellular morphology, tissue organization, and pathological changes]</p>
            
            <h3>Scientific Interpretation</h3>
            <p>[Provide scientific analysis correlating findings with clinical presentation and evidence-based interpretation]</p>
            
            <h3>Pathological Conclusion</h3>
            <p>[Final pathological assessment with confidence levels and recommendations]</p>
        `;
    }
    
    getRadiologyTemplate() {
        return `
            <h2>Imaging Analysis Report</h2>
            <h3>Examination Details</h3>
            <p><strong>Imaging Modality:</strong> [CT/MRI/X-ray/Ultrasound]</p>
            <p><strong>Study Date:</strong> [Examination date]</p>
            <p><strong>Contrast Used:</strong> [Yes/No - specify type]</p>
            
            <h3>Technical Parameters</h3>
            <p>[Document technical aspects including image quality and acquisition parameters]</p>
            
            <h3>Imaging Findings</h3>
            <p>[Systematic description of findings by anatomical region]</p>
            
            <h3>Quantitative Analysis</h3>
            <p>[Provide measurements and quantitative data where applicable]</p>
            
            <h3>Scientific Assessment</h3>
            <p>[Evidence-based interpretation correlating findings with clinical presentation]</p>
        `;
    }
    
    getLaboratoryTemplate() {
        return `
            <h2>Laboratory Analysis Report</h2>
            <h3>Test Parameters</h3>
            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <thead>
                    <tr style="background-color: #28a745; color: white;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Test Name</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Result</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Reference Range</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">[Test Parameter]</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">[Result Value]</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">[Normal Range]</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">[Normal/Abnormal]</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Statistical Analysis</h3>
            <p>[Provide statistical interpretation including variance analysis and correlation coefficients]</p>
            
            <h3>Scientific Interpretation</h3>
            <p>[Detailed scientific analysis including biochemical significance and physiological implications]</p>
        `;
    }
    
    validateForm() {
        const confirmAccuracy = document.getElementById('confirm-accuracy');
        if (confirmAccuracy && !confirmAccuracy.checked) {
            this.showNotification('Please confirm the accuracy of the report before submitting.', 'error');
            return false;
        }
        
        const wordCount = this.getWordCount();
        if (wordCount < 50) {
            this.showNotification('Report content must be at least 50 words long.', 'error');
            return false;
        }
        
        // Validate required fields
        const caseSelect = document.getElementById('case_id');
        if (caseSelect && !caseSelect.value) {
            this.showNotification('Please select a case for this report.', 'error');
            return false;
        }
        
        const hospitalSelect = document.getElementById('hospital_id');
        if (hospitalSelect && !hospitalSelect.value) {
            this.showNotification('Please select a hospital.', 'error');
            return false;
        }
        
        return true;
    }
    
    autoSave() {
        if (!this.editor || this.getWordCount() === 0) return;
        
        this.updateHiddenField();
        this.showNotification('Draft auto-saved', 'info', 2000);
    }
    
    saveDraft() {
        this.updateHiddenField();
        this.showNotification('Draft saved successfully!', 'success');
        
        // Here you could make an AJAX call to save the draft
        // For now, we'll just update the hidden field
    }
    
    saveAndPreview() {
        if (!this.validateForm()) return;
        
        this.updateHiddenField();
        
        // Change form action to preview
        const originalAction = this.form.action;
        this.form.action = originalAction.replace('/add', '/preview').replace('/edit', '/preview');
        this.form.target = '_blank';
        this.form.submit();
        
        // Restore original action
        this.form.action = originalAction;
        this.form.target = '';
    }
    
    previewReport() {
        if (this.getWordCount() === 0) {
            this.showNotification('Please add some content before previewing.', 'warning');
            return;
        }
        
        // Open preview in new window
        const previewWindow = window.open('', 'reportPreview', 'width=800,height=600,scrollbars=yes');
        
        const previewContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Report Preview</title>
                <style>
                    body { font-family: 'Times New Roman', serif; line-height: 1.6; padding: 20px; }
                    h2, h3 { color: #2c3e50; }
                    table { border-collapse: collapse; width: 100%; margin: 15px 0; }
                    table th, table td { border: 1px solid #ddd; padding: 8px; }
                    table th { background-color: #f8f9fa; }
                </style>
            </head>
            <body>
                <h1>Report Preview</h1>
                <hr>
                ${this.editor.innerHTML}
                <hr>
                <p><small>Word Count: ${this.getWordCount()} | Character Count: ${this.getCharacterCount()}</small></p>
            </body>
            </html>
        `;
        
        previewWindow.document.write(previewContent);
        previewWindow.document.close();
    }
    
    showNotification(message, type = 'info', duration = 5000) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, duration);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a reports page with an editor
    if (document.getElementById('report-editor')) {
        new ReportsEditor();
    }
});

// Export for use in other scripts
window.ReportsEditor = ReportsEditor;