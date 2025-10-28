<?php
/**
 * Doctor Layout
 * Layout file for doctor role interfaces
 *
 * @var \App\View\AppView $this
 */

$cakeDescription = 'Medical Dashboard - Doctor Portal';
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
    <!-- Doctor Panel CSS -->
    <?php echo $this->Html->css('/assets/doctor/css/doctor.css') ?>
    
    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body class="bg-light">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark doctor-navbar sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                <i class="fas fa-stethoscope me-2"></i>Doctor Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($this->request->getParam('controller') === 'Dashboard') ? 'active' : ''; ?>" 
                           href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($this->request->getParam('controller') === 'Cases') ? 'active' : ''; ?>" 
                           href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index']) ?>">
                            <i class="fas fa-folder-open me-1"></i>My Cases
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-doctor me-1"></i>
                            <?php 
                            $firstName = $this->request->getSession()->read('Auth.User.first_name');
                            $lastName = $this->request->getSession()->read('Auth.User.last_name');
                            echo h($firstName && $lastName ? 'Dr. ' . $firstName . ' ' . $lastName : 'Doctor');
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Profile', 'action' => 'view']) ?>">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Profile', 'action' => 'settings']) ?>">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $this->Url->build(array('prefix' => 'Doctor', 'controller' => 'Login', 'action' => 'logout')) ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area with Sidebar -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-white sidebar border-end">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-muted">
                        <span>CASE MANAGEMENT</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($this->request->getParam('controller') === 'Dashboard') ? 'active' : ''; ?>" 
                               href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($this->request->getParam('controller') === 'Cases' && $this->request->getParam('action') === 'index') ? 'active' : ''; ?>" 
                               href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index']) ?>">
                                <i class="fas fa-folder-open me-2"></i>
                                All Cases
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>FILTER BY STATUS</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'assigned']]) ?>">
                                <i class="fas fa-clipboard-check me-2 text-info"></i>
                                Assigned
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'in_progress']]) ?>">
                                <i class="fas fa-spinner me-2 text-warning"></i>
                                In Progress
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'completed']]) ?>">
                                <i class="fas fa-check-circle me-2 text-success"></i>
                                Completed
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'cancelled']]) ?>">
                                <i class="fas fa-times-circle me-2 text-danger"></i>
                                Cancelled
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>FILTER BY PRIORITY</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['priority' => 'urgent']]) ?>">
                                <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                                Urgent
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['priority' => 'high']]) ?>">
                                <i class="fas fa-arrow-up me-2 text-warning"></i>
                                High Priority
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['priority' => 'medium']]) ?>">
                                <i class="fas fa-minus me-2 text-info"></i>
                                Medium
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index', '?' => ['priority' => 'low']]) ?>">
                                <i class="fas fa-arrow-down me-2 text-secondary"></i>
                                Low Priority
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content py-4">
                    <div class="flash-messages">
                        <?php echo $this->Flash->render() ?>
                    </div>
                    <?php echo $this->fetch('content') ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-top mt-5 py-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">© <?php echo date('Y') ?> Medical Dashboard - Doctor Portal</small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Current Hospital: <?php echo h($this->request->getSession()->read('Hospital.current.name') ?? 'Not Set') ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flash Messages JS -->
    <?php echo $this->Html->script('flash-messages') ?>
    <!-- Doctor Panel JS -->
    <?php echo $this->Html->script('/assets/doctor/js/doctor.js') ?>
    
    <?php echo $this->fetch('script') ?>
</body>
</html>