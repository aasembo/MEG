/**
 * Custom Rich Text Editor - Google Docs Style
 * Free and open-source alternative to TinyMCE
 * Built with Bootstrap 5 and vanilla JavaScript
 */

class RichTextEditor {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            placeholder: 'Start typing...',
            toolbar: [
                'fontFamily', 'fontSize', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'textColor', 'backgroundColor', '|',
                'alignLeft', 'alignCenter', 'alignRight', 'alignJustify', '|',
                'heading1', 'heading2', 'heading3', 'paragraph', '|',
                'bulletList', 'orderedList', 'indent', 'outdent', '|',
                'link', 'table', '|',
                'undo', 'redo', '|',
                'fullscreen'
            ],
            height: '200px',
            fontFamilies: [
                'Times New Roman', 'Arial', 'Georgia', 'Helvetica', 'Verdana', 'Calibri'
            ],
            fontSizes: ['8', '10', '12', '14', '16', '18', '20', '24', '28', '32'],
            colors: [
                '#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#efefef', '#f3f3f3', '#ffffff',
                '#980000', '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#4a86e8', '#0000ff', '#9900ff', '#ff00ff',
                '#e6b8af', '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#c9daf8', '#cfe2f3', '#d9d2e9', '#ead1dc'
            ],
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.createEditor();
        this.createToolbar();
        this.attachEvents();
        this.setupKeyboardShortcuts();
        this.isFullscreen = false;
    }
    
    createEditor() {
        // Hide original textarea
        this.element.style.display = 'none';
        
        // Create editor container
        this.container = document.createElement('div');
        this.container.className = 'border rounded bg-white position-relative';
        
        // Create toolbar container
        this.toolbarContainer = document.createElement('div');
        this.toolbarContainer.className = 'border-bottom bg-light p-2 d-flex flex-wrap align-items-center gap-1';
        
        // Create editor content area
        this.editorContent = document.createElement('div');
        this.editorContent.className = 'p-3';
        this.editorContent.contentEditable = true;
        this.editorContent.innerHTML = this.element.value || `<p>${this.options.placeholder}</p>`;
        this.editorContent.style.cssText = `
            min-height: ${this.options.height};
            outline: none;
            line-height: 1.6;
            font-family: 'Times New Roman', serif;
            font-size: 14px;
        `;
        
        // Add placeholder behavior
        if (!this.element.value) {
            this.editorContent.classList.add('text-muted');
        }
        
        // Assemble editor
        this.container.appendChild(this.toolbarContainer);
        this.container.appendChild(this.editorContent);
        this.element.parentNode.insertBefore(this.container, this.element);
    }
    
    createToolbar() {
        const toolbarButtons = {
            fontFamily: { type: 'select', title: 'Font family', options: this.options.fontFamilies },
            fontSize: { type: 'select', title: 'Font size', options: this.options.fontSizes },
            bold: { icon: '<strong>B</strong>', title: 'Bold (Ctrl+B)', command: 'bold' },
            italic: { icon: '<em>I</em>', title: 'Italic (Ctrl+I)', command: 'italic' },
            underline: { icon: '<u>U</u>', title: 'Underline (Ctrl+U)', command: 'underline' },
            strikethrough: { icon: '<s>S</s>', title: 'Strikethrough', command: 'strikethrough' },
            textColor: { type: 'color', title: 'Text color', command: 'foreColor' },
            backgroundColor: { type: 'color', title: 'Background color', command: 'backColor' },
            alignLeft: { icon: '⬅', title: 'Align left', command: 'justifyLeft' },
            alignCenter: { icon: '↔', title: 'Align center', command: 'justifyCenter' },
            alignRight: { icon: '➡', title: 'Align right', command: 'justifyRight' },
            alignJustify: { icon: '⬌', title: 'Justify', command: 'justifyFull' },
            heading1: { icon: 'H1', title: 'Heading 1', command: 'formatBlock', value: 'h1' },
            heading2: { icon: 'H2', title: 'Heading 2', command: 'formatBlock', value: 'h2' },
            heading3: { icon: 'H3', title: 'Heading 3', command: 'formatBlock', value: 'h3' },
            paragraph: { icon: 'P', title: 'Paragraph', command: 'formatBlock', value: 'p' },
            bulletList: { icon: '• •', title: 'Bullet list', command: 'insertUnorderedList' },
            orderedList: { icon: '1. 2.', title: 'Numbered list', command: 'insertOrderedList' },
            indent: { icon: '→', title: 'Increase indent', command: 'indent' },
            outdent: { icon: '←', title: 'Decrease indent', command: 'outdent' },
            link: { icon: '🔗', title: 'Insert link', command: 'createLink' },
            table: { icon: '⊞', title: 'Insert table', command: 'insertTable' },
            undo: { icon: '↶', title: 'Undo (Ctrl+Z)', command: 'undo' },
            redo: { icon: '↷', title: 'Redo (Ctrl+Y)', command: 'redo' },
            fullscreen: { icon: '⛶', title: 'Fullscreen', command: 'toggleFullscreen' }
        };
        
        this.options.toolbar.forEach(item => {
            if (item === '|') {
                // Add separator
                const separator = document.createElement('div');
                separator.className = 'border-start mx-1';
                separator.style.height = '24px';
                this.toolbarContainer.appendChild(separator);
            } else if (toolbarButtons[item]) {
                const element = this.createToolbarElement(toolbarButtons[item], item);
                this.toolbarContainer.appendChild(element);
            }
        });
    }
    
    createToolbarElement(config, key) {
        if (config.type === 'select') {
            return this.createSelectDropdown(config, key);
        } else if (config.type === 'color') {
            return this.createColorPicker(config, key);
        } else {
            return this.createToolbarButton(config, key);
        }
    }
    
    createSelectDropdown(config, key) {
        const dropdown = document.createElement('div');
        dropdown.className = 'dropdown';
        
        const button = document.createElement('button');
        button.className = 'btn btn-sm btn-outline-secondary dropdown-toggle';
        button.type = 'button';
        button.setAttribute('data-bs-toggle', 'dropdown');
        button.title = config.title;
        
        if (key === 'fontFamily') {
            button.textContent = 'Times New Roman';
            button.style.minWidth = '140px';
        } else if (key === 'fontSize') {
            button.textContent = '14';
            button.style.minWidth = '60px';
        }
        
        const menu = document.createElement('ul');
        menu.className = 'dropdown-menu';
        
        config.options.forEach(option => {
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.className = 'dropdown-item';
            link.href = '#';
            link.textContent = option;
            
            if (key === 'fontFamily') {
                link.style.fontFamily = option;
            } else if (key === 'fontSize') {
                link.style.fontSize = option + 'px';
            }
            
            link.addEventListener('click', (e) => {
                e.preventDefault();
                if (key === 'fontFamily') {
                    this.executeCommand('fontName', option);
                    button.textContent = option;
                } else if (key === 'fontSize') {
                    this.executeCommand('fontSize', this.convertToFontSize(option));
                    button.textContent = option;
                }
                this.editorContent.focus();
            });
            
            item.appendChild(link);
            menu.appendChild(item);
        });
        
        dropdown.appendChild(button);
        dropdown.appendChild(menu);
        return dropdown;
    }
    
    createColorPicker(config, key) {
        const dropdown = document.createElement('div');
        dropdown.className = 'dropdown';
        
        const button = document.createElement('button');
        button.className = 'btn btn-sm btn-outline-secondary dropdown-toggle';
        button.type = 'button';
        button.setAttribute('data-bs-toggle', 'dropdown');
        button.title = config.title;
        button.innerHTML = key === 'textColor' ? 'A' : '🎨';
        
        const menu = document.createElement('div');
        menu.className = 'dropdown-menu p-2';
        menu.style.width = '200px';
        
        const colorGrid = document.createElement('div');
        colorGrid.className = 'd-flex flex-wrap gap-1';
        
        this.options.colors.forEach(color => {
            const colorBtn = document.createElement('button');
            colorBtn.type = 'button';
            colorBtn.className = 'btn p-0 border';
            colorBtn.style.cssText = `
                width: 20px;
                height: 20px;
                background-color: ${color};
            `;
            colorBtn.title = color;
            
            colorBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.executeCommand(config.command, color);
                this.editorContent.focus();
            });
            
            colorGrid.appendChild(colorBtn);
        });
        
        menu.appendChild(colorGrid);
        dropdown.appendChild(button);
        dropdown.appendChild(menu);
        return dropdown;
    }
    
    createToolbarButton(config, key) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-outline-secondary';
        button.innerHTML = config.icon;
        button.title = config.title;
        
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            if (config.command === 'createLink') {
                this.insertLink();
            } else if (config.command === 'insertTable') {
                this.insertTable();
            } else if (config.command === 'toggleFullscreen') {
                this.toggleFullscreen();
            } else {
                this.executeCommand(config.command, config.value);
            }
            
            this.updateToolbarState();
            this.editorContent.focus();
        });
        
        return button;
    }
    
    convertToFontSize(size) {
        // Convert font size to HTML size (1-7)
        const sizeMap = {
            '8': '1', '10': '1', '12': '2', '14': '3', 
            '16': '4', '18': '5', '20': '6', '24': '7', 
            '28': '7', '32': '7'
        };
        return sizeMap[size] || '3';
    }
    
    insertLink() {
        const url = prompt('Enter URL:');
        if (url) {
            this.executeCommand('createLink', url);
        }
    }
    
    insertTable() {
        const rows = prompt('Number of rows:', '3');
        const cols = prompt('Number of columns:', '3');
        
        if (rows && cols) {
            let tableHTML = '<table class="table table-bordered"><tbody>';
            
            for (let r = 0; r < parseInt(rows); r++) {
                tableHTML += '<tr>';
                for (let c = 0; c < parseInt(cols); c++) {
                    tableHTML += '<td>&nbsp;</td>';
                }
                tableHTML += '</tr>';
            }
            
            tableHTML += '</tbody></table>';
            this.executeCommand('insertHTML', tableHTML);
        }
    }
    
    toggleFullscreen() {
        if (!this.isFullscreen) {
            // Enter fullscreen
            this.originalParent = this.container.parentNode;
            this.originalPosition = this.container.style.position;
            this.originalZIndex = this.container.style.zIndex;
            
            this.container.style.position = 'fixed';
            this.container.style.top = '0';
            this.container.style.left = '0';
            this.container.style.width = '100vw';
            this.container.style.height = '100vh';
            this.container.style.zIndex = '9999';
            this.container.classList.add('bg-white');
            
            this.editorContent.style.height = 'calc(100vh - 60px)';
            
            document.body.appendChild(this.container);
            this.isFullscreen = true;
        } else {
            // Exit fullscreen
            this.container.style.position = this.originalPosition;
            this.container.style.top = '';
            this.container.style.left = '';
            this.container.style.width = '';
            this.container.style.height = '';
            this.container.style.zIndex = this.originalZIndex;
            this.container.classList.remove('bg-white');
            
            this.editorContent.style.height = this.options.height;
            
            this.originalParent.appendChild(this.container);
            this.isFullscreen = false;
        }
    }
    
    executeCommand(command, value = null) {
        this.editorContent.focus();
        
        if (command === 'formatBlock') {
            // Handle heading formatting
            document.execCommand(command, false, `<${value}>`);
        } else {
            document.execCommand(command, false, value);
        }
        
        this.updateContent();
    }
    
    attachEvents() {
        // Update hidden textarea when content changes
        this.editorContent.addEventListener('input', () => {
            this.updateContent();
            this.handlePlaceholder();
            this.updateToolbarState();
        });
        
        // Handle focus/blur for placeholder
        this.editorContent.addEventListener('focus', () => {
            this.handlePlaceholder();
        });
        
        this.editorContent.addEventListener('blur', () => {
            this.handlePlaceholder();
        });
        
        // Update toolbar state on selection change
        this.editorContent.addEventListener('keyup', () => {
            this.updateToolbarState();
        });
        
        this.editorContent.addEventListener('mouseup', () => {
            this.updateToolbarState();
        });
        
        // Handle focus styles
        this.editorContent.addEventListener('focus', () => {
            this.container.classList.add('border-primary');
        });
        
        this.editorContent.addEventListener('blur', () => {
            this.container.classList.remove('border-primary');
        });
        
        // Prevent form submission on toolbar button clicks
        this.toolbarContainer.addEventListener('mousedown', (e) => {
            e.preventDefault();
        });
        
        // Handle escape key for fullscreen
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isFullscreen) {
                this.toggleFullscreen();
            }
        });
    }
    
    setupKeyboardShortcuts() {
        this.editorContent.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key.toLowerCase()) {
                    case 'b':
                        e.preventDefault();
                        this.executeCommand('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        this.executeCommand('italic');
                        break;
                    case 'u':
                        e.preventDefault();
                        this.executeCommand('underline');
                        break;
                    case 'z':
                        if (e.shiftKey) {
                            e.preventDefault();
                            this.executeCommand('redo');
                        } else {
                            e.preventDefault();
                            this.executeCommand('undo');
                        }
                        break;
                    case 'y':
                        e.preventDefault();
                        this.executeCommand('redo');
                        break;
                    case 'k':
                        e.preventDefault();
                        this.insertLink();
                        break;
                }
            }
        });
    }
    
    handlePlaceholder() {
        const isEmpty = this.editorContent.textContent.trim() === '' || 
                       this.editorContent.innerHTML === '<p><br></p>' ||
                       this.editorContent.innerHTML === '<div><br></div>';
        
        if (isEmpty && !this.editorContent.matches(':focus')) {
            this.editorContent.innerHTML = `<p>${this.options.placeholder}</p>`;
            this.editorContent.classList.add('text-muted');
        } else if (this.editorContent.textContent.trim() === this.options.placeholder) {
            if (this.editorContent.matches(':focus')) {
                this.editorContent.innerHTML = '<p><br></p>';
                this.editorContent.classList.remove('text-muted');
            }
        } else {
            this.editorContent.classList.remove('text-muted');
        }
    }
    
    updateContent() {
        // Clean up the HTML and update the original textarea
        let content = this.editorContent.innerHTML;
        
        // Remove placeholder text
        if (content === `<p>${this.options.placeholder}</p>`) {
            content = '';
        }
        
        // Clean up empty paragraphs at the end
        content = content.replace(/<p><br><\/p>$/g, '');
        
        this.element.value = content;
        
        // Trigger change event
        const event = new Event('change', { bubbles: true });
        this.element.dispatchEvent(event);
    }
    
    updateToolbarState() {
        const buttons = this.toolbarContainer.querySelectorAll('.btn');
        
        buttons.forEach(button => {
            button.classList.remove('active', 'btn-primary');
            button.classList.add('btn-outline-secondary');
        });
        
        // Check which formatting is active
        const commands = ['bold', 'italic', 'underline', 'strikethrough'];
        commands.forEach(command => {
            if (document.queryCommandState(command)) {
                const button = this.toolbarContainer.querySelector(`[title*="${command}"]`);
                if (button) {
                    button.classList.remove('btn-outline-secondary');
                    button.classList.add('btn-primary', 'active');
                }
            }
        });
        
        // Update alignment buttons
        const alignments = ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'];
        alignments.forEach(alignment => {
            if (document.queryCommandState(alignment)) {
                const alignButton = this.toolbarContainer.querySelector(`[title*="${alignment.replace('justify', 'Align ').toLowerCase()}"]`);
                if (alignButton) {
                    alignButton.classList.remove('btn-outline-secondary');
                    alignButton.classList.add('btn-primary', 'active');
                }
            }
        });
    }
    
    // Public methods
    getContent() {
        return this.element.value;
    }
    
    setContent(html) {
        this.editorContent.innerHTML = html;
        this.updateContent();
        this.handlePlaceholder();
    }
    
    focus() {
        this.editorContent.focus();
    }
    
    destroy() {
        if (this.container) {
            this.container.remove();
            this.element.style.display = '';
        }
    }
}

// Auto-initialize rich text editors
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all textareas with rich-text-editor class
    document.querySelectorAll('textarea.rich-text-editor').forEach(textarea => {
        new RichTextEditor(textarea, {
            height: textarea.dataset.height || '200px',
            placeholder: textarea.placeholder || 'Start typing...'
        });
    });
    
    // Initialize Bootstrap dropdowns after a short delay
    setTimeout(() => {
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(dropdown => {
                new bootstrap.Dropdown(dropdown);
            });
        }
    }, 100);
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RichTextEditor;
}