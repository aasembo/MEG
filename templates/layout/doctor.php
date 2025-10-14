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
    <nav class="navbar navbar-expand-lg navbar-dark doctor-navbar sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                <i class="fas fa-stethoscope me-2"></i>Medical Dashboard
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="patientsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-injured me-1"></i>Patients
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Patients', 'action' => 'index']) ?>">
                                <i class="fas fa-list me-2"></i>All Patients
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Patients', 'action' => 'add']) ?>">
                                <i class="fas fa-plus me-2"></i>Add Patient
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="appointmentsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-check me-1"></i>Appointments
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Appointments', 'action' => 'index']) ?>">
                                <i class="fas fa-calendar me-2"></i>View Appointments
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Appointments', 'action' => 'schedule']) ?>">
                                <i class="fas fa-plus me-2"></i>Schedule Appointment
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-md me-1"></i><?php echo h($this->request->getSession()->read('Auth.User.first_name') . ' ' . $this->request->getSession()->read('Auth.User.last_name')) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Profile', 'action' => 'view']) ?>">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Profile', 'action' => 'settings']) ?>">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Doctor', 'controller' => 'Auth', 'action' => 'logout']) ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-lg-12 px-md-4 doctor-content">
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