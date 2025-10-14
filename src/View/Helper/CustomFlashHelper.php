<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;
use Cake\View\Helper\FlashHelper as CakeFlashHelper;

/**
 * Custom Flash helper for Bootstrap-styled flash messages
 */
class CustomFlashHelper extends Helper
{
    /**
     * Default helpers
     */
    protected array $helpers = ['Html'];

    /**
     * Flash message icons mapping
     */
    protected array $icons = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
        'default' => 'fas fa-bell',
    ];

    /**
     * Bootstrap alert class mapping
     */
    protected array $alertClasses = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        'default' => 'alert-primary',
    ];

    /**
     * Render flash messages with Bootstrap styling
     *
     * @param string $key Flash message key
     * @param array $options Options array
     * @return string HTML output
     */
    public function render(string $key = 'flash', array $options = []): string
    {
        $flash = $this->getView()->getRequest()->getSession()->consume('Flash.' . $key);
        
        if (empty($flash)) {
            return '';
        }

        $out = '';
        foreach ($flash as $message) {
            $out .= $this->renderMessage($message, $options);
        }

        return $out;
    }

    /**
     * Render a single flash message
     *
     * @param array $message Message data
     * @param array $options Options array
     * @return string HTML output
     */
    protected function renderMessage(array $message, array $options = []): string
    {
        $type = $message['element'] ?? 'default';
        $text = $message['message'] ?? '';
        $params = $message['params'] ?? [];

        // Determine alert class and icon
        $alertClass = $this->alertClasses[$type] ?? $this->alertClasses['default'];
        $icon = $this->icons[$type] ?? $this->icons['default'];

        // Build CSS classes
        $classes = ['alert', $alertClass, 'alert-dismissible', 'fade', 'show'];
        if (isset($options['class'])) {
            $classes[] = $options['class'];
        }
        if (isset($params['class'])) {
            $classes[] = $params['class'];
        }

        // Auto-dismiss option
        $autoDismiss = $params['autoDismiss'] ?? true;
        if ($autoDismiss) {
            $classes[] = 'auto-dismiss';
        }

        // Build attributes
        $attributes = [
            'class' => implode(' ', $classes),
            'role' => 'alert',
        ];

        if (isset($params['id'])) {
            $attributes['id'] = $params['id'];
        }

        // Build message content
        $content = '<div class="d-flex align-items-center">';
        $content .= '<i class="' . $icon . ' alert-icon"></i>';
        $content .= '<div class="flex-grow-1">' . h($text) . '</div>';
        $content .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $content .= '</div>';

        return $this->Html->div(null, $content, $attributes);
    }

    /**
     * Render all flash messages in a container
     *
     * @param array $options Container options
     * @return string HTML output
     */
    public function renderAll(array $options = []): string
    {
        $session = $this->getView()->getRequest()->getSession();
        $flashData = $session->read('Flash') ?? [];
        
        if (empty($flashData)) {
            return '';
        }

        $containerClass = $options['containerClass'] ?? 'flash-messages';
        $output = '<div class="' . $containerClass . '">';
        
        foreach ($flashData as $key => $messages) {
            $output .= $this->render($key, $options);
        }
        
        $output .= '</div>';

        // Add auto-dismiss JavaScript
        $output .= $this->getAutoDismissScript();

        return $output;
    }

    /**
     * Get JavaScript for auto-dismissing flash messages
     *
     * @return string JavaScript code
     */
    protected function getAutoDismissScript(): string
    {
        return '<script>
        document.addEventListener("DOMContentLoaded", function() {
            // Auto-dismiss flash messages after 5 seconds
            const autoDismissAlerts = document.querySelectorAll(".alert.auto-dismiss");
            autoDismissAlerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.classList.add("fade-out");
                        setTimeout(function() {
                            if (alert.parentNode) {
                                alert.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            });

            // Smooth close animation
            const closeButtons = document.querySelectorAll(".alert .btn-close");
            closeButtons.forEach(function(button) {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    const alert = this.closest(".alert");
                    alert.classList.add("fade-out");
                    setTimeout(function() {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                });
            });
        });
        </script>';
    }
}