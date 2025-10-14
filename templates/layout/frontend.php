<?php
/**
 * Frontend Layout
 *
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo $this->fetch('title') ?> - MEG Healthcare Platform
    </title>
    <?php echo $this->Html->meta('icon') ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <?php echo $this->Html->css(['frontend']) ?>

    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <?php 
            $hospitalName = isset($currentHospital) ? $currentHospital->name : 'MEG Healthcare';
            ?>
            <?php echo $this->Html->link(
                '<i class="fas fa-hospital-alt me-2"></i>' . h($hospitalName),
                ['controller' => 'Pages', 'action' => 'home'],
                ['class' => 'navbar-brand fw-bold text-primary', 'escape' => false]
            ) ?>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-home me-1"></i>Home',
                            ['controller' => 'Pages', 'action' => 'home'],
                            ['class' => 'nav-link', 'escape' => false]
                        ) ?>
                    </li>
                    <li class="nav-item">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-info-circle me-1"></i>About',
                            ['controller' => 'Pages', 'action' => 'display', 'about'],
                            ['class' => 'nav-link', 'escape' => false]
                        ) ?>
                    </li>
                    <li class="nav-item">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-stethoscope me-1"></i>Services',
                            ['controller' => 'Pages', 'action' => 'display', 'services'],
                            ['class' => 'nav-link', 'escape' => false]
                        ) ?>
                    </li>
                    <li class="nav-item">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-envelope me-1"></i>Contact',
                            ['controller' => 'Pages', 'action' => 'display', 'contact'],
                            ['class' => 'nav-link', 'escape' => false]
                        ) ?>
                    </li>
                </ul>
                
                <!-- Right side navigation -->
                <ul class="navbar-nav">
                    <?php $identity = $this->request->getAttribute('identity'); ?>
                    <?php if ($identity): ?>
                        <!-- User is logged in -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo h($identity->get('first_name')) ?> <?php echo h($identity->get('last_name')) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Account</h6></li>
                                <li>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-tachometer-alt me-2"></i>Dashboard',
                                        ['controller' => 'Dashboard', 'action' => 'index'],
                                        ['class' => 'dropdown-item', 'escape' => false]
                                    ) ?>
                                </li>
                                <li>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-user me-2"></i>Profile',
                                        ['controller' => 'Users', 'action' => 'profile'],
                                        ['class' => 'dropdown-item', 'escape' => false]
                                    ) ?>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-sign-out-alt me-2"></i>Logout',
                                        ['controller' => 'Auth', 'action' => 'logout'],
                                        ['class' => 'dropdown-item text-danger', 'escape' => false]
                                    ) ?>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- User is not logged in -->
                        
                        <li class="nav-item">
                            <?php echo $this->Html->link(
                                '<i class="fas fa-sign-in-alt me-1"></i>Login',
                                '#login-section',
                                ['class' => 'nav-link', 'escape' => false]
                            ) ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <?php echo $this->Flash->render() ?>
        <?php echo $this->fetch('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold">
                        <i class="fas fa-hospital-alt me-2"></i>MEG Healthcare
                    </h5>
                    <p class="text-muted">
                        Advanced healthcare platform connecting doctors, nurses, scientists, patients, and technicians 
                        for better healthcare outcomes.
                    </p>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li>
                            <?php echo $this->Html->link('Home', ['controller' => 'Pages', 'action' => 'home'], ['class' => 'text-muted text-decoration-none']) ?>
                        </li>
                        <li>
                            <?php echo $this->Html->link('About', ['controller' => 'Pages', 'action' => 'display', 'about'], ['class' => 'text-muted text-decoration-none']) ?>
                        </li>
                        <li>
                            <?php echo $this->Html->link('Services', ['controller' => 'Pages', 'action' => 'display', 'services'], ['class' => 'text-muted text-decoration-none']) ?>
                        </li>
                        <li>
                            <?php echo $this->Html->link('Contact', ['controller' => 'Pages', 'action' => 'display', 'contact'], ['class' => 'text-muted text-decoration-none']) ?>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold">User Types</h6>
                    <ul class="list-unstyled">
                        <li><span class="text-muted">Doctors</span></li>
                        <li><span class="text-muted">Nurses</span></li>
                        <li><span class="text-muted">Scientists</span></li>
                        <li><span class="text-muted">Patients</span></li>
                        <li><span class="text-muted">Technicians</span></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold">Contact Information</h6>
                    <ul class="list-unstyled">
                        <li class="text-muted">
                            <i class="fas fa-envelope me-2"></i>
                            info@meghealthcare.com
                        </li>
                        <li class="text-muted">
                            <i class="fas fa-phone me-2"></i>
                            +1 (555) 123-4567
                        </li>
                        <li class="text-muted">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Healthcare Innovation Center
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; <?php echo date('Y') ?> MEG Healthcare Platform. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        Secure Healthcare Platform with Okta Authentication
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Frontend JavaScript -->
    <?php echo $this->Html->script('frontend') ?>
    
    <?php echo $this->fetch('script') ?>
</body>
</html>