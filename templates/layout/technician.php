<?php
/**
 * Technician Layout
 * Layout file for technician role interfaces
 *
 * @var \App\View\AppView $this
 */

$cakeDescription = 'Technical Dashboard - Technician Portal';
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
    <!-- Technician Panel CSS -->
    <?php echo $this->Html->css('/assets/technician/css/technician.css') ?>
    
    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body class="bg-light">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark technician-navbar sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                <i class="fas fa-tools me-2"></i>Technical Dashboard
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-medical me-1"></i>Cases
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index']) ?>"><i class="fas fa-list me-2"></i>All Cases</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'add']) ?>"><i class="fas fa-plus me-2"></i>New Case</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'draft']]) ?>"><i class="fas fa-edit me-2"></i>Draft Cases</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'assigned']]) ?>"><i class="fas fa-user-check me-2"></i>Assigned Cases</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-1"></i>Patients
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Patients', 'action' => 'index']) ?>"><i class="fas fa-list me-2"></i>All Patients</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Patients', 'action' => 'add']) ?>"><i class="fas fa-user-plus me-2"></i>Add Patient</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Patients', 'action' => 'index', '?' => ['status' => 'active']]) ?>"><i class="fas fa-user-check me-2"></i>Active Patients</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-clipboard-list me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                
                <!-- User Info & Logout -->
                <?php if ($this->getRequest()->getAttribute('identity')): ?>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo h($this->getRequest()->getAttribute('identity')->get('email') ?? $this->getRequest()->getAttribute('identity')->get('username') ?? 'Technician') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Account</h6></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-key me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Login', 'action' => 'logout']) ?>">
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
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-toolbox me-2"></i>Technical Tools
                            </h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']) ?>" 
                               class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-home me-2 text-primary"></i>Dashboard
                            </a>
                            <a href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index']) ?>" 
                               class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-file-medical me-2 text-success"></i>Cases
                            </a>
                            <a href="<?php echo $this->Url->build(['prefix' => 'Technician', 'controller' => 'Patients', 'action' => 'index']) ?>" 
                               class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-users me-2 text-info"></i>Patients
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>Reports
                            </a>
                        </div>
                    </div>
                    
                    <!-- System Status Card -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-server me-2"></i>System Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Network</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Devices</span>
                                <span class="badge bg-success">Connected</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Monitoring</span>
                                <span class="badge bg-info">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Flash Messages -->
                <div class="flash-messages">
                    <?php echo $this->Flash->render() ?>
                </div>
                
                <!-- Page Content -->
                <div class="content">
                    <?php echo $this->fetch('content') ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5 border-top">
        <div class="container">
            <small class="d-flex align-items-center justify-content-center">
                © <?php echo date('Y') ?> Technical Dashboard. 
                <i class="fas fa-cog text-secondary mx-1"></i> 
                Secure Healthcare Management Platform
            </small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flash Messages JS -->
    <?php echo $this->Html->script('flash-messages') ?>
    <!-- Technician Panel JS -->
    <?php echo $this->Html->script('/assets/technician/js/technician.js') ?>    <?php echo $this->fetch('script') ?>
</body>
</html>