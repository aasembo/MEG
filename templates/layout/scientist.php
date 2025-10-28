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
<html>
<head>
    <?php echo $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrfToken" content="<?php echo $this->request->getAttribute('csrfToken') ?>">
    <title>
        <?php echo $cakeDescription ?>:
        <?php echo $this->fetch('title') ?>
    </title>
    <?php echo $this->Html->meta('icon') ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Flash Messages CSS -->
    <?php echo $this->Html->css('flash-messages') ?>
    
    <?php echo $this->Html->css('/assets/scientist/css/scientist.css') ?>
    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg scientist-navbar">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-bold" href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index')) ?>">
                <i class="fas fa-microscope me-2"></i>
                Scientific Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index')) ?>">
                            <i class="fas fa-chart-line me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-medical me-1"></i>Cases
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index')) ?>"><i class="fas fa-list me-2"></i>My Assigned Cases</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index', '?' => array('status' => 'assigned'))) ?>"><i class="fas fa-user-check me-2"></i>Assigned Cases</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index', '?' => array('status' => 'in_progress'))) ?>"><i class="fas fa-spinner me-2"></i>In Progress Cases</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index', '?' => array('status' => 'completed'))) ?>"><i class="fas fa-check-circle me-2"></i>Completed Cases</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            <i class="fas fa-file-alt me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php 
                            $identity = $this->getRequest()->getAttribute('identity');
                            if ($identity) {
                                echo h($identity->get('email') ? $identity->get('email') : ($identity->get('username') ? $identity->get('username') : 'Scientist'));
                            } else {
                                echo 'Scientist';
                            }
                            ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-user-cog me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Login', 'action' => 'logout')) ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Quick Access
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index')) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Cases', 'action' => 'index')) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-file-medical me-2"></i>My Cases
                        </a>
                        <a href="#" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt me-2"></i>Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10 content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-microscope text-success me-2"></i>
                        <?php echo $this->fetch('title') ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <?php if ($this->fetch('breadcrumb')): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo $this->Url->build(array('prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index')) ?>">
                                <i class="fas fa-home"></i> Scientific Portal
                            </a>
                        </li>
                        <?php echo $this->fetch('breadcrumb') ?>
                    </ol>
                </nav>
                <?php endif; ?>

                <!-- Flash Messages -->
                <div class="flash-messages">
                    <?php echo $this->Flash->render() ?>
                </div>

                <!-- Page Content -->
                <?php echo $this->fetch('content') ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-4 bg-light text-center">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y') ?> Medical Laboratory System - Research Portal</p>
            <p class="mb-0 text-muted">
                <small>
                    <i class="fas fa-flask me-1"></i>
                    Scientist Interface | Version 1.0
                </small>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Flash Messages JS -->
    <?php echo $this->Html->script('flash-messages') ?>
    
    <?php echo $this->Html->script('/assets/scientist/js/scientist.js') ?>
    
    <?php echo $this->fetch('script') ?>
</body>
</html>