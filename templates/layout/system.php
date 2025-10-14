<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'System Admin Panel';
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

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Flash Messages CSS -->
    <?php echo $this->Html->css('flash-messages') ?>
    <!-- Admin CSS -->
    <?php echo $this->Html->css('/assets/admin/css/admin.css') ?>
    
    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body class="bg-light">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                <i class="fas fa-cogs me-2"></i>System Admin Panel
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-1"></i>Users
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Users', 'action' => 'index']) ?>"><i class="fas fa-list me-2"></i>All Users</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Users', 'action' => 'add']) ?>"><i class="fas fa-plus me-2"></i>Add User</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Users', 'action' => 'index', '?' => ['filter' => 'super']]) ?>"><i class="fas fa-user-shield me-2"></i>Super Users</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Users', 'action' => 'index', '?' => ['filter' => 'active']]) ?>"><i class="fas fa-user-check me-2"></i>Active Users</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Users', 'action' => 'index', '?' => ['filter' => 'inactive']]) ?>"><i class="fas fa-user-times me-2"></i>Inactive Users</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-hospital me-1"></i>Hospitals
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Hospitals', 'action' => 'index']) ?>"><i class="fas fa-list me-2"></i>All Hospitals</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Hospitals', 'action' => 'add']) ?>"><i class="fas fa-plus me-2"></i>Add Hospital</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Hospitals', 'action' => 'index', '?' => ['status' => 'active']]) ?>"><i class="fas fa-check-circle me-2"></i>Active Hospitals</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Hospitals', 'action' => 'index', '?' => ['status' => 'inactive']]) ?>"><i class="fas fa-pause-circle me-2"></i>Inactive Hospitals</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']) ?>">
                            <i class="fas fa-home me-1"></i>Frontend
                        </a>
                    </li>
                </ul>
                
                <!-- User Info & Logout -->
                <?php if ($this->getRequest()->getAttribute('identity')): ?>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo h($this->getRequest()->getAttribute('identity')->get('email')) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Account</h6></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Login', 'action' => 'logout']) ?>">
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
                                <i class="fas fa-bars me-2"></i>Quick Actions
                            </h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index']) ?>" 
                               class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-home me-2 text-primary"></i>Dashboard
                            </a>
                            <a href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Users', 'action' => 'index']) ?>" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-users me-2 text-success"></i>Manage Users
                            </a>
                            <a href="<?php echo $this->Url->build(['prefix' => 'System', 'controller' => 'Hospitals', 'action' => 'index']) ?>" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-hospital me-2 text-danger"></i>Manage Hospitals
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-chart-bar me-2 text-info"></i>Reports
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0">
                                <i class="fas fa-cogs me-2 text-warning"></i>System Settings
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
                                <span class="small text-muted">Server</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Database</span>
                                <span class="badge bg-success">Connected</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Cache</span>
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
                    <?php echo $this->CustomFlash->renderAll() ?>
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
                                © <?php echo date('Y') ?> System Admin Panel. 
                <i class="fas fa-heart text-danger mx-1"></i> 
                Secure Healthcare Management Platform
            </small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flash Messages JS -->
    <?php echo $this->Html->script('flash-messages') ?>
    <!-- System Admin Panel JS -->
    <?php echo $this->Html->script('/assets/admin/js/admin.js') ?>
    
    <?php echo $this->fetch('script') ?>
</body>
</html>