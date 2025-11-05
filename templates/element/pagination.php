<?php
/**
 * Universal Pagination Element
 * 
 * Role-based pagination component that adapts to different user contexts
 * Automatically detects the current role and applies appropriate styling
 * 
 * @var \App\View\AppView $this
 * @var string $prev Previous button text (optional)
 * @var string $next Next button text (optional) 
 * @var array $numbers Page numbers configuration (optional)
 * @var string $counter Counter text template (optional)
 * @var array $queryParams Query parameters to preserve (optional)
 */

// Get current controller and action for context
$controller = $this->request->getParam('controller');
$action = $this->request->getParam('action');
$plugin = $this->request->getParam('plugin');

// Determine the user role context
$isAdmin = ($plugin === 'Admin' || strpos($controller, 'Admin') !== false);
$isTechnician = ($controller === 'Technician' || strpos($controller, 'Technician') !== false);

// Default configuration
$defaults = [
    'prev' => '‹ Previous',
    'next' => 'Next ›',
    'counter' => 'Showing {{start}} to {{end}} of {{count}} entries',
    'queryParams' => [],
    'numbers' => [
        'modulus' => 4,
        'first' => 'First',
        'last' => 'Last'
    ]
];

// Extract variables with defaults
$prev = $prev ?? $defaults['prev'];
$next = $next ?? $defaults['next'];
$counter = $counter ?? $defaults['counter'];
$queryParams = $queryParams ?? $defaults['queryParams'];
$numbers = isset($numbers) ? array_merge($defaults['numbers'], $numbers) : $defaults['numbers'];

// Role-specific styling
if ($isAdmin) {
    $paginationClass = 'pagination pagination-sm mb-0 justify-content-end';
    $containerClass = 'bg-light';
    $textClass = 'text-muted';
} elseif ($isTechnician) {
    $paginationClass = 'pagination pagination-sm mb-0 justify-content-md-end justify-content-center';
    $containerClass = 'bg-light';
    $textClass = 'text-muted';
} else {
    // Default/general styling
    $paginationClass = 'pagination pagination-sm mb-0 justify-content-center';
    $containerClass = 'bg-light';
    $textClass = 'text-muted';
}

// Pagination templates for consistent Bootstrap styling
$paginationTemplates = [
    'first' => '<li class="page-item">{{text}}</li>',
    'firstDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>',
    'prevActive' => '<li class="page-item">{{text}}</li>',
    'prevDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>',
    'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'current' => '<li class="page-item active" aria-current="page"><span class="page-link">{{text}}</span></li>',
    'ellipsis' => '<li class="page-item disabled"><span class="page-link">...</span></li>',
    'nextActive' => '<li class="page-item">{{text}}</li>',
    'nextDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>',
    'last' => '<li class="page-item">{{text}}</li>',
    'lastDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>'
];

// Build URL options with preserved query parameters
$urlOptions = [];
if (!empty($queryParams)) {
    $urlOptions['?'] = $queryParams;
}
?>

<div class="card-footer <?php echo $containerClass ?>">
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <small class="<?php echo $textClass ?>">
                    <i class="fas fa-info-circle me-1"></i>
                    <?php echo $this->Paginator->counter($counter) ?>
                </small>
            </div>
        </div>
        <div class="col-md-6">
            <nav aria-label="Pagination navigation">
                <ul class="<?php echo $paginationClass ?>">
                    <?php 
                    // First page button
                    if (!empty($numbers['first'])) {
                        echo $this->Paginator->first(
                            '<i class="fas fa-angle-double-left"></i>', 
                            [
                                'escape' => false,
                                'class' => 'page-link',
                                'url' => $urlOptions,
                                'templates' => $paginationTemplates,
                                'title' => 'First page'
                            ]
                        );
                    }
                    
                    // Previous button
                    echo $this->Paginator->prev(
                        ($isAdmin || $isTechnician) ? '<i class="fas fa-chevron-left"></i>' : $prev,
                        [
                            'escape' => false,
                            'class' => 'page-link',
                            'url' => $urlOptions,
                            'templates' => $paginationTemplates,
                            'title' => 'Previous page'
                        ]
                    );
                    
                    // Page numbers
                    echo $this->Paginator->numbers([
                        'modulus' => $numbers['modulus'],
                        'first' => false, // Handled separately above
                        'last' => false,  // Handled separately below
                        'class' => 'page-link',
                        'url' => $urlOptions,
                        'before' => '',
                        'after' => '',
                        'templates' => $paginationTemplates
                    ]);
                    
                    // Next button
                    echo $this->Paginator->next(
                        ($isAdmin || $isTechnician) ? '<i class="fas fa-chevron-right"></i>' : $next,
                        [
                            'escape' => false,
                            'class' => 'page-link',
                            'url' => $urlOptions,
                            'templates' => $paginationTemplates,
                            'title' => 'Next page'
                        ]
                    );
                    
                    // Last page button
                    if (!empty($numbers['last'])) {
                        echo $this->Paginator->last(
                            '<i class="fas fa-angle-double-right"></i>', 
                            [
                                'escape' => false,
                                'class' => 'page-link',
                                'url' => $urlOptions,
                                'templates' => $paginationTemplates,
                                'title' => 'Last page'
                            ]
                        );
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>