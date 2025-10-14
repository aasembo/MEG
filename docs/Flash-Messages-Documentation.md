# Flash Messages Documentation

## Bootstrap Flash Messages Design

Your CakePHP application now has enhanced Bootstrap flash messages with the following features:

### Features
- **Bootstrap 5 Styling**: Professional alert components with proper colors and styling
- **Font Awesome Icons**: Visual indicators for different message types
- **Auto-dismiss**: Messages automatically disappear after 5 seconds (configurable)
- **Manual Dismiss**: Users can close messages with the X button
- **Pause on Hover**: Auto-dismiss pauses when user hovers over message
- **Smooth Animations**: Fade in/out transitions
- **Responsive Design**: Works on all screen sizes
- **Accessibility**: ARIA attributes and keyboard support
- **Dark Theme Support**: Adapts to user's color scheme preference

### Message Types
- **Success** (green): `alert-success` with check-circle icon
- **Error/Danger** (red): `alert-danger` with exclamation-circle icon  
- **Warning** (yellow): `alert-warning` with exclamation-triangle icon
- **Info** (blue): `alert-info` with info-circle icon
- **Primary** (blue): `alert-primary` with info-circle icon (default)

### Usage in Controllers

#### Basic Flash Messages
```php
// Success message
$this->Flash->success('Patient saved successfully!');

// Error message  
$this->Flash->error('Failed to save patient information.');

// Warning message
$this->Flash->warning('Please review the patient information.');

// Info message
$this->Flash->info('Patient record has been updated.');
```

#### Custom Parameters
```php
// Custom icon
$this->Flash->success('Operation completed!', [
    'params' => ['icon' => 'fas fa-check-double']
]);

// Disable auto-dismiss
$this->Flash->error('Critical error occurred!', [
    'params' => ['autoDismiss' => false]
]);

// Custom CSS classes
$this->Flash->info('Welcome back!', [
    'params' => ['class' => 'flash-large']
]);

// Non-dismissible message
$this->Flash->warning('System maintenance in progress', [
    'params' => ['dismissible' => false]
]);
```

### JavaScript Usage

#### Programmatic Flash Messages
```javascript
// Show success message
showSuccess('Data saved successfully!');

// Show error message
showError('Connection failed. Please try again.');

// Show warning with custom options
showWarning('Session expires in 5 minutes', {
    autoDismiss: false,
    icon: 'fas fa-clock'
});

// Show info message
showInfo('New features available!');

// Direct FlashManager usage
FlashManager.showMessage('Custom message', 'primary', {
    icon: 'fas fa-bell',
    class: 'flash-large',
    autoDismiss: true
});
```

#### AJAX Integration
```javascript
// Form with automatic flash handling
<form data-flash-on-submit="true" action="/patients/save" method="post">
    <!-- form fields -->
</form>

// Manual AJAX with flash response
fetch('/api/patients', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.flash) {
        FlashManager.showMessage(data.flash.message, data.flash.type);
    }
});
```

### CSS Customization

#### Available CSS Classes
- `.flash-messages` - Container for all flash messages
- `.flash-auto-dismiss` - Messages that auto-dismiss
- `.flash-large` - Larger padding and font size
- `.flash-small` - Smaller padding and font size
- `.flash-no-icon` - Hide the icon
- `.flash-fixed` - Fixed position (top-right overlay)
- `.flash-toast` - Toast-style messages

#### Custom Styling Example
```css
/* Custom success message styling */
.alert-success.my-custom-success {
    background: linear-gradient(135deg, #d1e7dd, #a3d5b7);
    border-left-width: 6px;
    font-weight: 500;
}

/* Custom icon styling */
.flash-icon.fa-custom {
    color: #ff6b35;
    animation: pulse 1s infinite;
}
```

### Configuration Options

#### JavaScript Configuration
```javascript
// Modify global configuration
FlashManager.config.autoDismissDelay = 7000; // 7 seconds
FlashManager.config.pauseOnHover = false;
FlashManager.config.stackLimit = 3;
```

### Template Usage

#### Layout Integration
The flash messages are automatically included in your layouts:
- Technician Layout: `templates/layout/technician.php`
- Admin Layout: `templates/layout/admin.php`  
- Doctor Layout: `templates/layout/doctor.php`
- Scientist Layout: `templates/layout/scientist.php`
- System Layout: `templates/layout/system.php`

#### Custom Flash Elements
Flash message elements are located in `templates/element/flash/`:
- `default.php` - Default/primary messages
- `success.php` - Success messages
- `error.php` - Error/danger messages  
- `warning.php` - Warning messages
- `info.php` - Info messages

### Advanced Features

#### Multiple Messages
```php
$this->Flash->success('Patient saved!');
$this->Flash->info('Email notification sent.');
$this->Flash->warning('Please verify contact information.');
```

#### Custom Flash Helper
The application includes a `CustomFlashHelper` that provides additional functionality:
```php
// In your controller
$this->viewBuilder()->setHelper('CustomFlash');

// In your template
echo $this->CustomFlash->renderAll();
```

#### Fixed Position Messages
```javascript
// Show fixed position message (top-right overlay)
const message = showSuccess('File uploaded successfully!');
message.classList.add('flash-fixed');
```

### Best Practices

1. **Use appropriate message types** - Success for confirmations, error for failures, warning for cautions, info for updates
2. **Keep messages concise** - Users should be able to read them quickly
3. **Use auto-dismiss for non-critical messages** - But disable it for important errors
4. **Provide meaningful icons** - They help users quickly understand message context
5. **Test accessibility** - Ensure messages work with screen readers
6. **Consider mobile users** - Messages should be readable on small screens

### Browser Support
- Modern browsers with ES6+ support
- Bootstrap 5 compatible browsers
- Font Awesome 6 compatible browsers
- CSS Grid and Flexbox support required