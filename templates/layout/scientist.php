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
            <a class="navbar-brand text-white fw-bold" href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'dashboard']) ?>">
                <i class="fas fa-microscope me-2"></i>
                Research Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'dashboard']) ?>">
                            <i class="fas fa-chart-line me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'samples']) ?>">
                            <i class="fas fa-vial me-1"></i>Samples
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'research']) ?>">
                            <i class="fas fa-flask me-1"></i>Research
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'reports']) ?>">
                            <i class="fas fa-file-alt me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo isset($authUser) ? h($authUser['username']) : 'Scientist' ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'profile']) ?>">
                                <i class="fas fa-user-cog me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'settings']) ?>">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['controller' => 'Pages', 'action' => 'logout']) ?>">
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
                        <a href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'dashboard']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'samples']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-vial me-2"></i>Sample Management
                        </a>
                        <a href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'research']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-flask me-2"></i>Research Data
                        </a>
                        <a href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'analysis']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2"></i>Data Analysis
                        </a>
                        <a href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'reports']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt me-2"></i>Reports
                        </a>
                        <a href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'collaboration']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i>Collaboration
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
                            <a href="<?php echo $this->Url->build(['controller' => 'Scientist', 'action' => 'dashboard']) ?>">
                                <i class="fas fa-home"></i> Research Portal
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