<?php
/**
 * Scientist Layout
 * Layout file for scientist role interfaces
 *
 * @var \App\View\AppView $this
 */

$cakeDescription = 'Research Dashboard - Scientist Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php echo $cakeDescription ?>:
        <?php echo $this->fetch('title') ?>
    </title>
    <?php echo $this->Html->meta('icon') ?>
    
    <!-- CSRF Token for AJAX requests -->
    <meta name="csrfToken" content="<?php echo $this->request->getAttribute('csrfToken') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Flash Messages CSS -->
    <?php echo $this->Html->css('flash-messages') ?>
    <!-- Scientist Panel CSS -->
    <?php echo $this->Html->css('/assets/scientist/css/scientist.css') ?>
    
    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body class="bg-light">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                <i class="fas fa-microscope me-2"></i>
                <span>MEG Research Portal</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-vial me-1"></i>Manage Cases
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index']) ?>">
                                <i class="fas fa-list me-2 text-success"></i>All Cases
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'assigned']]) ?>">
                                <i class="fas fa-user-check me-2 text-info"></i>Assigned to Me
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'in_progress']]) ?>">
                                <i class="fas fa-spinner me-2 text-warning"></i>In Progress
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'review']]) ?>">
                                <i class="fas fa-search me-2 text-primary"></i>Under Review
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'completed']]) ?>">
                                <i class="fas fa-check-circle me-2 text-success"></i>Completed
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-alt me-1"></i>Manage Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Reports', 'action' => 'index']) ?>">
                                <i class="fas fa-list me-2 text-success"></i>All Reports
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Reports', 'action' => 'add']) ?>">
                                <i class="fas fa-plus me-2 text-success"></i>Create Report
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Reports', 'action' => 'index', '?' => ['status' => 'pending']]) ?>">
                                <i class="fas fa-clock me-2 text-warning"></i>Pending Review
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Reports', 'action' => 'index', '?' => ['status' => 'reviewed']]) ?>">
                                <i class="fas fa-eye me-2 text-info"></i>Under Review
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Reports', 'action' => 'index', '?' => ['status' => 'approved']]) ?>">
                                <i class="fas fa-check-double me-2 text-success"></i>Approved
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- User Info & Logout -->
                <?php if ($this->getRequest()->getAttribute('identity')): ?>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <span class="d-none d-lg-inline">
                                <?php echo h($this->getRequest()->getAttribute('identity')->get('name') ?? $this->getRequest()->getAttribute('identity')->get('username') ?? 'Scientist') ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">
                                <i class="fas fa-user me-2"></i>Account
                            </h6></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-id-card me-2 text-success"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2 text-secondary"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Login', 'action' => 'logout']) ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar border-end vh-100 position-sticky top-0">
                <div class="position-sticky pt-3">
                    <!-- Quick Navigation -->
                    <div class="mb-3">
                        <h6 class="px-3 mb-3 text-uppercase text-muted small fw-bold">
                            <i class="fas fa-compass me-2"></i>Navigation
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-dark" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                                    <i class="fas fa-home me-3 text-success"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-dark" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index']) ?>">
                                    <i class="fas fa-vial me-3 text-info"></i>
                                    <span>Manage Cases</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-dark" href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Reports', 'action' => 'index']) ?>">
                                    <i class="fas fa-file-alt me-3 text-warning"></i>
                                    <span>Manage Reports</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mb-3">
                        <h6 class="px-3 mb-3 text-uppercase text-muted small fw-bold">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                        <div class="px-3">
                            <a href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index']) ?>" 
                               class="btn btn-success btn-sm w-100 mb-2">
                                <i class="fas fa-search me-2"></i>Review Cases
                            </a>
                            <a href="<?php echo $this->Url->build(['prefix' => 'Scientist', 'controller' => 'Reports', 'action' => 'index']) ?>" 
                               class="btn btn-outline-warning btn-sm w-100">
                                <i class="fas fa-file-alt me-2"></i>Manage Reports
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Flash Messages -->
                <?php echo $this->Flash->render() ?>
                
                <!-- Page Content -->
                <?php echo $this->fetch('content') ?>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white text-center text-muted py-4 mt-5 border-top">
        <div class="container">
            <div class="d-flex align-items-center justify-content-center">
                <i class="fas fa-microscope text-success me-2"></i>
                <small>
                    © <?php echo date('Y') ?> MEG Research Platform - Advanced Scientific Analysis System
                </small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flash Messages JS -->
    <?php echo $this->Html->script('flash-messages') ?>
    <!-- Scientist Panel JS -->
    <?php echo $this->Html->script('/assets/scientist/js/scientist.js?v=' . time()) ?>
    
    <?php echo $this->fetch('script') ?>
</body>
</html>
