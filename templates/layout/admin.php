<?php

$cakeDescription = 'Hospital Admin Panel';
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
    
    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body class="bg-light">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                <i class="fas fa-shield-alt me-2 text-warning"></i>
                <span class="text-warning">Hospital</span> <span class="text-white">Admin</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-1"></i>Users
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index']) ?>"><i class="fas fa-list me-2"></i>All Users</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'add']) ?>"><i class="fas fa-plus me-2"></i>Add User</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index', '?' => ['status' => 'active']]) ?>"><i class="fas fa-user-check me-2"></i>Active Users</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index', '?' => ['status' => 'inactive']]) ?>"><i class="fas fa-user-times me-2"></i>Inactive Users</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Cases', 'action' => 'index']) ?>">
                            <i class="fas fa-briefcase-medical me-1"></i>Cases
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-stethoscope me-1"></i>Medical Data
                        </a>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">Basic Management</h6></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Departments', 'action' => 'index']) ?>"><i class="fas fa-building me-2"></i>Departments</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Modalities', 'action' => 'index']) ?>"><i class="fas fa-x-ray me-2"></i>Modalities</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Sedations', 'action' => 'index']) ?>"><i class="fas fa-syringe me-2"></i>Sedations</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Advanced Management</h6></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Procedures', 'action' => 'index']) ?>"><i class="fas fa-procedures me-2"></i>Procedures</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Exams', 'action' => 'index']) ?>"><i class="fas fa-file-medical me-2"></i>Exams</a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'ExamsProcedures', 'action' => 'index']) ?>"><i class="fas fa-link me-2"></i>Exam Procedures</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Reports', 'action' => 'index']) ?>">
                                <i class="fas fa-list me-2"></i>All Reports
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Reports', 'action' => 'index', '?' => ['status' => 'pending']]) ?>">
                                <i class="fas fa-clock me-2 text-warning"></i>Pending
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Reports', 'action' => 'index', '?' => ['status' => 'reviewed']]) ?>">
                                <i class="fas fa-eye me-2 text-info"></i>Reviewed
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Reports', 'action' => 'index', '?' => ['status' => 'approved']]) ?>">
                                <i class="fas fa-check-double me-2 text-success"></i>Approved
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index']) ?>">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                    </li>
                </ul>
                
                <!-- User Info & Logout -->
                <?php if ($this->getRequest()->getAttribute('identity')): ?>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1 text-warning"></i>
                            <?php echo h($this->getRequest()->getAttribute('identity')->get('email')) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Account</h6></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'logout']) ?>">
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
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar border-end vh-100 position-sticky top-0">
                <div class="position-sticky pt-3">
                    <!-- Quick Navigation -->
                    <div class="mb-3">
                        <h6 class="px-3 mb-3 text-uppercase text-warning small fw-bold">
                            <i class="fas fa-shield-alt me-2"></i>Administration
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-light" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index']) ?>">
                                    <i class="fas fa-tachometer-alt me-3 text-warning"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-light" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index']) ?>">
                                    <i class="fas fa-users me-3 text-info"></i>
                                    <span>Users</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-light" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Cases', 'action' => 'index']) ?>">
                                    <i class="fas fa-briefcase-medical me-3 text-success"></i>
                                    <span>Cases</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-light" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Departments', 'action' => 'index']) ?>">
                                    <i class="fas fa-building me-3 text-primary"></i>
                                    <span>Departments</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-light" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Reports', 'action' => 'index']) ?>">
                                    <i class="fas fa-chart-bar me-3 text-warning"></i>
                                    <span>Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-2 text-light" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index']) ?>">
                                    <i class="fas fa-cogs me-3 text-secondary"></i>
                                    <span>Settings</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mb-3">
                        <h6 class="px-3 mb-3 text-uppercase text-warning small fw-bold">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                        <div class="px-3">
                            <a href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'add']) ?>" 
                               class="btn btn-warning btn-sm w-100 mb-2 text-dark fw-bold">
                                <i class="fas fa-user-plus me-2"></i>Add User
                            </a>
                            <a href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index']) ?>" 
                               class="btn btn-outline-warning btn-sm w-100 mb-2">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                            <a href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Departments', 'action' => 'index']) ?>" 
                               class="btn btn-outline-light btn-sm w-100">
                                <i class="fas fa-building me-2"></i>Departments
                            </a>
                        </div>
                    </div>

                    <!-- Medical Data Management -->
                    <div class="mb-3">
                        <h6 class="px-3 mb-3 text-uppercase text-warning small fw-bold">
                            <i class="fas fa-stethoscope me-2"></i>Medical Data
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-1 text-light small" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Exams', 'action' => 'index']) ?>">
                                    <i class="fas fa-file-medical me-3 text-info"></i>
                                    <span>Exams</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-1 text-light small" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Procedures', 'action' => 'index']) ?>">
                                    <i class="fas fa-procedures me-3 text-success"></i>
                                    <span>Procedures</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-1 text-light small" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Modalities', 'action' => 'index']) ?>">
                                    <i class="fas fa-x-ray me-3 text-primary"></i>
                                    <span>Modalities</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center px-3 py-1 text-light small" href="<?php echo $this->Url->build(['prefix' => 'Admin', 'controller' => 'Sedations', 'action' => 'index']) ?>">
                                    <i class="fas fa-syringe me-3 text-warning"></i>
                                    <span>Sedations</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
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
    <footer class="bg-white text-center text-muted py-4 mt-5 border-top">
        <div class="container">
            <div class="d-flex align-items-center justify-content-center">
                <i class="fas fa-shield-alt text-warning me-2"></i>
                <small>
                    © <?php echo date('Y') ?> Hospital Admin Panel • 
                    <span class="text-warning">Secure Healthcare Management Platform</span>
                </small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php echo $this->fetch('script') ?>
</body>
</html>