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
                'link', 'table', 'insertHTML', '|',
                'pasteSpecial', 'clearFormatting', '|',
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
            insertHTML: { icon: '&lt;/&gt;', title: 'Insert HTML', command: 'insertHTML' },
            pasteSpecial: { icon: '📋', title: 'Paste Special - preserves formatting (Ctrl+Shift+V)', command: 'pasteSpecial' },
            clearFormatting: { icon: '🧹', title: 'Clear formatting', command: 'removeFormat' },
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
            } else if (config.command === 'insertHTML') {
                this.showInsertHTMLDialog();
            } else if (config.command === 'toggleFullscreen') {
                this.toggleFullscreen();
            } else if (config.command === 'pasteSpecial') {
                this.showPasteSpecialDialog();
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
        
        // Enhanced paste handling for formatted content
        this.editorContent.addEventListener('paste', (e) => {
            this.handlePaste(e);
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
    
    handlePaste(e) {
        // Get clipboard data
        const clipboardData = e.clipboardData || window.clipboardData;
        
        // Try to get HTML content first (preserves formatting)
        let htmlContent = clipboardData.getData('text/html');
        let textContent = clipboardData.getData('text/plain');
        
        console.log('Paste detected:', { hasHTML: !!htmlContent, hasText: !!textContent });
        
        // If we have HTML content, allow it with minimal cleaning
        if (htmlContent && htmlContent.trim()) {
            console.log('Handling as HTML from clipboard');
            // Prevent default behavior only if we're going to handle it
            e.preventDefault();
            
            // Clean the HTML content but preserve most formatting
            htmlContent = this.cleanPastedHTML(htmlContent);
            
            // Insert the cleaned HTML
            this.insertHTMLAtCursor(htmlContent);
            
            // Update content and trigger events
            this.updateContent();
            this.handlePlaceholder();
            this.updateToolbarState();
        } else if (textContent && textContent.trim()) {
            // Check if the plain text is actually HTML code
            const trimmedText = textContent.trim();
            const looksLikeHTML = (trimmedText.startsWith('<') && trimmedText.includes('>')) || 
                                  (trimmedText.includes('<') && trimmedText.includes('</'));
            
            console.log('Text content:', trimmedText.substring(0, 100));
            console.log('Looks like HTML?', looksLikeHTML);
            
            if (looksLikeHTML) {
                console.log('Treating plain text as HTML');
                // Plain text contains HTML code - render it as HTML
                e.preventDefault();
                
                // Clean and insert as HTML
                const cleanedHTML = this.cleanPastedHTML(trimmedText);
                console.log('Cleaned HTML:', cleanedHTML.substring(0, 100));
                this.insertHTMLAtCursor(cleanedHTML);
                
                // Update content and trigger events
                this.updateContent();
                this.handlePlaceholder();
                this.updateToolbarState();
            } else {
                console.log('Treating as plain text');
                // Plain text only - prevent default and format
                e.preventDefault();
                
                // Convert line breaks to paragraphs
                const formattedText = this.formatPlainText(textContent);
                this.insertHTMLAtCursor(formattedText);
                
                // Update content and trigger events
                this.updateContent();
                this.handlePlaceholder();
                this.updateToolbarState();
            }
        }
        // If no content, allow default paste behavior
    }
    
    cleanPastedHTML(html) {
        console.log('cleanPastedHTML: Starting with HTML length:', html.length);
        
        // Check if this is a full HTML document (has DOCTYPE or html tags)
        const isFullDocument = html.trim().toLowerCase().includes('<!doctype') || 
                               html.trim().toLowerCase().startsWith('<html');
        
        if (isFullDocument) {
            console.log('Detected full HTML document, extracting body content...');
            // Parse as full document and extract body content
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Get body content
            const bodyContent = doc.body ? doc.body.innerHTML : html;
            console.log('Extracted body content length:', bodyContent.length);
            html = bodyContent;
        }
        
        // Create a temporary div to parse HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        console.log('Temp div children:', tempDiv.children.length);
        
        // Only remove truly unwanted elements (scripts, styles, meta)
        this.removeUnwantedElements(tempDiv);
        
        // Light cleaning of attributes - preserve more formatting
        this.cleanAttributesLightly(tempDiv);
        
        // Convert Word-specific elements but preserve formatting
        this.convertWordElements(tempDiv);
        
        const result = tempDiv.innerHTML;
        console.log('cleanPastedHTML: Final HTML length:', result.length);
        
        return result;
    }
    
    removeUnwantedElements(element) {
        // Only remove dangerous/problematic elements, not empty ones
        const unwantedTags = ['script', 'style', 'meta', 'link', 'xml'];
        
        unwantedTags.forEach(tag => {
            const elements = element.querySelectorAll(tag);
            elements.forEach(el => el.remove());
        });
        
        // Remove o:p tags and other Word-specific namespaced elements (Word specific empty tags)
        // Use getElementsByTagName instead of querySelectorAll for namespaced elements
        const allElements = element.getElementsByTagName('*');
        const elementsToRemove = [];
        
        for (let i = 0; i < allElements.length; i++) {
            const el = allElements[i];
            // Check if tag name contains a colon (namespaced element like o:p, w:sdt, etc.)
            if (el.tagName.includes(':')) {
                elementsToRemove.push(el);
            }
        }
        
        elementsToRemove.forEach(el => el.remove());
    }
    
    
    cleanAttributesLightly(element) {
        // This is a lighter cleaning that preserves most HTML formatting
        // Only remove dangerous attributes and scripts
        const dangerousAttributes = ['onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur'];
        
        const allElements = element.querySelectorAll('*');
        allElements.forEach(el => {
            // Remove dangerous event handler attributes
            dangerousAttributes.forEach(attr => {
                if (el.hasAttribute(attr)) {
                    el.removeAttribute(attr);
                }
            });
            
            // Clean Microsoft Office specific attributes but keep others
            const attributes = Array.from(el.attributes);
            attributes.forEach(attr => {
                // Remove MS Office specific attributes
                if (attr.name.startsWith('mso-') || attr.name.startsWith('o:')) {
                    el.removeAttribute(attr.name);
                }
            });
            
            // Keep style attributes but clean MS Office styles
            if (el.hasAttribute('style')) {
                const style = el.getAttribute('style');
                const cleanedStyle = style
                    .split(';')
                    .filter(s => !s.trim().startsWith('mso-'))
                    .join(';');
                
                if (cleanedStyle.trim()) {
                    el.setAttribute('style', cleanedStyle);
                } else {
                    el.removeAttribute('style');
                }
            }
            
            // Handle images - remove broken or external images that might cause issues
            if (el.tagName.toLowerCase() === 'img') {
                const src = el.getAttribute('src');
                if (src) {
                    // Check if it's a data URL (base64) - allow these
                    if (src.startsWith('data:image/')) {
                        // Keep data URLs
                    } else if (src.startsWith('http://') || src.startsWith('https://')) {
                        // External URLs - you can choose to keep or remove
                        // For now, we'll replace with a placeholder to avoid broken image errors
                        console.warn('External image detected and removed:', src);
                        el.setAttribute('alt', `[External image: ${src.substring(0, 50)}...]`);
                        el.removeAttribute('src');
                    } else if (!src.startsWith('/')) {
                        // Relative URLs that might be broken
                        console.warn('Potentially broken relative image:', src);
                    }
                }
            }
        });
    }
    
    cleanAttributes(element) {
        // List of allowed attributes
        const allowedAttributes = {
            'p': ['style'],
            'div': ['style'],
            'span': ['style'],
            'strong': [],
            'b': [],
            'em': [],
            'i': [],
            'u': [],
            'strike': [],
            's': [],
            'h1': ['style'],
            'h2': ['style'],
            'h3': ['style'],
            'h4': ['style'],
            'h5': ['style'],
            'h6': ['style'],
            'ul': [],
            'ol': [],
            'li': [],
            'a': ['href', 'title', 'target'],
            'img': ['src', 'alt', 'title', 'width', 'height', 'style'],
            'table': ['class', 'style'],
            'thead': [],
            'tbody': [],
            'tr': [],
            'td': ['colspan', 'rowspan', 'style'],
            'th': ['colspan', 'rowspan', 'style'],
            'br': []
        };
        
        // Clean all elements
        const allElements = element.querySelectorAll('*');
        allElements.forEach(el => {
            const tagName = el.tagName.toLowerCase();
            const allowedAttrs = allowedAttributes[tagName] || [];
            
            // Remove all attributes except allowed ones
            const attributes = Array.from(el.attributes);
            attributes.forEach(attr => {
                if (!allowedAttrs.includes(attr.name)) {
                    el.removeAttribute(attr.name);
                }
            });
            
            // Clean style attributes
            if (el.hasAttribute('style')) {
                el.setAttribute('style', this.cleanStyleAttribute(el.getAttribute('style')));
            }
        });
    }
    
    cleanStyleAttribute(style) {
        // List of allowed CSS properties
        const allowedProperties = [
            'font-weight', 'font-style', 'text-decoration', 'color', 
            'background-color', 'text-align', 'font-size', 'font-family',
            'margin', 'padding', 'line-height'
        ];
        
        // Parse style string
        const styles = style.split(';').filter(s => s.trim());
        const cleanedStyles = [];
        
        styles.forEach(styleRule => {
            const [property, value] = styleRule.split(':').map(s => s.trim());
            if (allowedProperties.includes(property) && value) {
                cleanedStyles.push(`${property}: ${value}`);
            }
        });
        
        return cleanedStyles.join('; ');
    }
    
    convertWordElements(element) {
        // Convert Word-specific paragraph spacing
        const paragraphs = element.querySelectorAll('p');
        paragraphs.forEach(p => {
            // Remove Word-specific classes
            p.removeAttribute('class');
            
            // Convert Word paragraph styles
            const style = p.getAttribute('style') || '';
            if (style.includes('margin')) {
                // Keep basic margin styles but clean them up
                const newStyle = style
                    .replace(/margin-top:\s*[^;]+;?/g, '')
                    .replace(/margin-bottom:\s*[^;]+;?/g, '')
                    .replace(/mso-[^:;]+:[^;]+;?/g, '') // Remove Microsoft Office styles
                    .trim();
                
                if (newStyle) {
                    p.setAttribute('style', newStyle);
                } else {
                    p.removeAttribute('style');
                }
            }
        });
        
        // Convert Word lists
        const listItems = element.querySelectorAll('p[style*="text-indent"]');
        listItems.forEach(item => {
            // This is a basic conversion - more complex list handling could be added
            const style = item.getAttribute('style') || '';
            if (style.includes('text-indent')) {
                item.removeAttribute('style');
            }
        });
    }
    
    formatPlainText(text) {
        // Convert plain text to HTML with proper paragraph formatting
        return text
            .split('\n\n')
            .map(paragraph => paragraph.trim())
            .filter(paragraph => paragraph.length > 0)
            .map(paragraph => `<p>${paragraph.replace(/\n/g, '<br>')}</p>`)
            .join('');
    }
    
    insertHTMLAtCursor(html) {
        console.log('insertHTMLAtCursor called with HTML length:', html.length);
        
        // Ensure the editor is focused
        this.editorContent.focus();
        
        // Get current selection
        const selection = window.getSelection();
        
        console.log('Selection range count:', selection.rangeCount);
        
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            
            // Check if the range is within our editor
            if (!this.editorContent.contains(range.commonAncestorContainer)) {
                console.log('Range not in editor, creating new range at end');
                // If not in our editor, create a range at the end
                const newRange = document.createRange();
                newRange.selectNodeContents(this.editorContent);
                newRange.collapse(false); // Collapse to end
                selection.removeAllRanges();
                selection.addRange(newRange);
            }
            
            const activeRange = selection.getRangeAt(0);
            
            console.log('Range found, deleting contents...');
            // Delete current selection
            activeRange.deleteContents();
            
            console.log('Creating fragment from HTML...');
            // Create a document fragment from the HTML
            const fragment = activeRange.createContextualFragment(html);
            
            console.log('Fragment created, nodes:', fragment.childNodes.length);
            
            // Insert the fragment
            activeRange.insertNode(fragment);
            
            console.log('Fragment inserted successfully');
            
            // Move cursor to end of inserted content
            activeRange.collapse(false);
            selection.removeAllRanges();
            selection.addRange(activeRange);
        } else {
            console.log('No selection range, appending to editor content');
            // If no selection, append to the end
            this.editorContent.innerHTML += html;
        }
        
        console.log('Final editor HTML length:', this.editorContent.innerHTML.length);
        console.log('insertHTMLAtCursor completed');
    }
    
    
    showInsertHTMLDialog() {
        // Create a modal dialog for inserting HTML
        const modalHTML = `
            <div class="modal fade" id="insertHTMLModal" tabindex="-1" aria-labelledby="insertHTMLModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="insertHTMLModalLabel">
                                <i class="fas fa-code me-2"></i>Insert HTML
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Paste your HTML code below. It will be rendered in the editor with formatting preserved.
                            </p>
                            <textarea id="insertHTMLTextarea" class="form-control font-monospace" rows="12" 
                                placeholder="<p>Your HTML content here...</p>" 
                                style="font-size: 13px;"></textarea>
                            <div class="mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cleanHTMLBeforeInsert" checked>
                                    <label class="form-check-label" for="cleanHTMLBeforeInsert">
                                        Clean and sanitize HTML (remove scripts, dangerous attributes)
                                    </label>
                                </div>
                            </div>
                            <div class="mt-2 p-2 bg-light rounded small">
                                <strong>Tip:</strong> This is useful for pasting formatted content from other sources or inserting custom HTML structures.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="insertHTMLConfirm">
                                <i class="fas fa-check me-1"></i>Insert HTML
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if present
        const existingModal = document.getElementById('insertHTMLModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to document
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('insertHTMLModal'));
        modal.show();
        
        // Focus on textarea when modal is shown
        document.getElementById('insertHTMLModal').addEventListener('shown.bs.modal', () => {
            document.getElementById('insertHTMLTextarea').focus();
        });
        
        // Handle confirm button
        document.getElementById('insertHTMLConfirm').addEventListener('click', () => {
            const htmlContent = document.getElementById('insertHTMLTextarea').value;
            const shouldClean = document.getElementById('cleanHTMLBeforeInsert').checked;
            
            console.log('Insert HTML clicked:', { length: htmlContent.length, shouldClean });
            
            if (htmlContent.trim()) {
                // Hide modal first
                modal.hide();
                
                // Wait for modal to hide, then insert
                setTimeout(() => {
                    let processedHTML = htmlContent;
                    
                    if (shouldClean) {
                        // Clean the HTML for security
                        console.log('Cleaning HTML...');
                        processedHTML = this.cleanPastedHTML(htmlContent);
                        console.log('Cleaned HTML length:', processedHTML.length);
                        console.log('Cleaned HTML preview:', processedHTML.substring(0, 200));
                    } else {
                        console.log('Skipping HTML cleaning');
                    }
                    
                    // Focus the editor first
                    this.editorContent.focus();
                    
                    // Insert the HTML
                    console.log('Inserting HTML at cursor...');
                    this.insertHTMLAtCursor(processedHTML);
                    
                    // Update editor
                    console.log('Updating content...');
                    this.updateContent();
                    this.handlePlaceholder();
                    this.updateToolbarState();
                    
                    console.log('Editor content updated successfully');
                }, 300); // Wait for modal animation
            } else {
                console.warn('No HTML content to insert');
                modal.hide();
            }
        });
        
        // Clean up modal when hidden
        document.getElementById('insertHTMLModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('insertHTMLModal').remove();
        });
    }
    
    showPasteSpecialDialog() {
        // Create a modal dialog for paste special
        const modalHTML = `
            <div class="modal fade" id="pasteSpecialModal" tabindex="-1" aria-labelledby="pasteSpecialModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pasteSpecialModalLabel">Paste Special</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">Paste your content below. Formatting will be preserved and cleaned for medical reports.</p>
                            <textarea id="pasteSpecialTextarea" class="form-control" rows="8" placeholder="Paste your formatted content here..."></textarea>
                            <div class="mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="preserveFormatting" checked>
                                    <label class="form-check-label" for="preserveFormatting">
                                        Preserve formatting (bold, italic, lists, etc.)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cleanWordFormatting" checked>
                                    <label class="form-check-label" for="cleanWordFormatting">
                                        Clean Microsoft Word formatting
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="pasteSpecialConfirm">Insert Content</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if present
        const existingModal = document.getElementById('pasteSpecialModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to document
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('pasteSpecialModal'));
        modal.show();
        
        // Focus on textarea when modal is shown
        document.getElementById('pasteSpecialModal').addEventListener('shown.bs.modal', () => {
            document.getElementById('pasteSpecialTextarea').focus();
        });
        
        // Handle confirm button
        document.getElementById('pasteSpecialConfirm').addEventListener('click', () => {
            const content = document.getElementById('pasteSpecialTextarea').value;
            const preserveFormatting = document.getElementById('preserveFormatting').checked;
            const cleanWordFormatting = document.getElementById('cleanWordFormatting').checked;
            
            if (content.trim()) {
                this.processPasteSpecialContent(content, preserveFormatting, cleanWordFormatting);
            }
            
            modal.hide();
        });
        
        // Clean up modal when hidden
        document.getElementById('pasteSpecialModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('pasteSpecialModal').remove();
        });
    }
    
    processPasteSpecialContent(content, preserveFormatting, cleanWordFormatting) {
        let processedContent = content;
        
        if (preserveFormatting) {
            // Try to detect if this is HTML content
            if (content.includes('<') && content.includes('>')) {
                // Treat as HTML
                if (cleanWordFormatting) {
                    processedContent = this.cleanPastedHTML(content);
                } else {
                    // Basic HTML cleaning
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = content;
                    this.removeUnwantedElements(tempDiv);
                    processedContent = tempDiv.innerHTML;
                }
            } else {
                // Treat as formatted plain text
                processedContent = this.formatPlainText(content);
            }
        } else {
            // Plain text only
            processedContent = this.formatPlainText(content);
        }
        
        // Insert the processed content
        this.insertHTMLAtCursor(processedContent);
        
        // Update editor
        this.updateContent();
        this.handlePlaceholder();
        this.updateToolbarState();
        
        // Focus back on editor
        this.editorContent.focus();
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
                    case 'v':
                        if (e.shiftKey) {
                            e.preventDefault();
                            this.showPasteSpecialDialog();
                        }
                        // Regular paste (Ctrl+V) will be handled by the paste event
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
        
        console.log('handlePlaceholder:', { 
            isEmpty, 
            textLength: this.editorContent.textContent.trim().length,
            htmlLength: this.editorContent.innerHTML.length,
            isFocused: this.editorContent.matches(':focus')
        });
        
        if (isEmpty && !this.editorContent.matches(':focus')) {
            console.log('Setting placeholder');
            this.editorContent.innerHTML = `<p>${this.options.placeholder}</p>`;
            this.editorContent.classList.add('text-muted');
        } else if (this.editorContent.textContent.trim() === this.options.placeholder) {
            if (this.editorContent.matches(':focus')) {
                console.log('Clearing placeholder on focus');
                this.editorContent.innerHTML = '<p><br></p>';
                this.editorContent.classList.remove('text-muted');
            }
        } else {
            console.log('Content exists, removing text-muted class');
            this.editorContent.classList.remove('text-muted');
        }
    }
    
    updateContent() {
        // Clean up the HTML and update the original textarea
        let content = this.editorContent.innerHTML;
        
        console.log('updateContent called, content length:', content.length);
        
        // Remove placeholder text
        if (content === `<p>${this.options.placeholder}</p>`) {
            content = '';
        }
        
        // Clean up empty paragraphs at the end
        content = content.replace(/<p><br><\/p>$/g, '');
        
        console.log('Setting textarea value, length:', content.length);
        this.element.value = content;
        
        console.log('Textarea value set:', this.element.value.length);
        
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