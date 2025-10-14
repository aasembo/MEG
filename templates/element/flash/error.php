<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */

// Default settings for error messages
$icon = $params['icon'] ?? 'fas fa-exclamation-circle';
$dismissible = $params['dismissible'] ?? true;
$autoDismiss = $params['autoDismiss'] ?? false; // Error messages stay longer

// Build CSS classes
$classes = ['alert', 'alert-danger'];
if ($dismissible) {
    $classes[] = 'alert-dismissible';
}
if ($autoDismiss) {
    $classes[] = 'flash-auto-dismiss';
}
if (!empty($params['class'])) {
    $classes[] = $params['class'];
}

// Escape message if needed
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="<?php echo implode(' ', $classes) ?>" role="alert">
    <div class="d-flex align-items-start">
        <i class="<?php echo $icon ?> flash-icon me-2 mt-1"></i>
        <div class="flex-grow-1"><?php echo $message ?></div>
        <?php if ($dismissible): ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php endif; ?>
    </div>
</div>
