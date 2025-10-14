<?php
/**
 * Homepage Template
 * 
 * @var \App\View\AppView $this
 * @var array $roleTypes
 * @var array $stats
 */
$this->assign('title', 'Welcome to MEG Healthcare Platform');
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">
                    Advanced MEG Healthcare Platform
                </h1>
                <p class="lead mb-4">
                    Cutting-edge magnetoencephalography technology for comprehensive brain monitoring 
                    and analysis. Secure, role-based access for healthcare professionals and patients.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Login Section -->
<section id="login-section" class="login-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">Access Enhanced Features (Optional)</h2>
                    <p class="lead text-muted">
                        Browse our platform freely, or login with Okta for personalized dashboard access.
                    </p>
                    <p class="text-info">
                        <small><i class="fas fa-info-circle me-1"></i>Login is completely optional - all core content is accessible without registration.</small>
                    </p>
                </div>
                
                <div class="row g-4 justify-content-center">
                    <!-- Doctor Login -->
                    <div class="col-lg-4 col-md-6">
                        <div class="login-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="login-icon mb-4">
                                    <i class="fas fa-user-md text-primary"></i>
                                </div>
                                <h4 class="card-title mb-3">Doctors</h4>
                                <p class="card-text text-muted mb-4">
                                    <strong>Access:</strong> Patient management, diagnosis tools, and medical records.
                                </p>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-sign-in-alt me-2"></i>Login as Doctor',
                                    ['prefix' => 'Doctor', 'controller' => 'Login', 'action' => 'login'],
                                    ['class' => 'btn btn-primary btn-lg w-100', 'escape' => false]
                                ) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scientist Login -->
                    <div class="col-lg-4 col-md-6">
                        <div class="login-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="login-icon mb-4">
                                    <i class="fas fa-microscope text-info"></i>
                                </div>
                                <h4 class="card-title mb-3">Scientists</h4>
                                <p class="card-text text-muted mb-4">
                                    <strong>Access:</strong> Research data analysis, study management, and clinical trials.
                                </p>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-sign-in-alt me-2"></i>Login as Scientist',
                                    ['prefix' => 'Scientist', 'controller' => 'Login', 'action' => 'login'],
                                    ['class' => 'btn btn-info btn-lg w-100', 'escape' => false]
                                ) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Technician Login -->
                    <div class="col-lg-4 col-md-6">
                        <div class="login-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="login-icon mb-4">
                                    <i class="fas fa-tools text-secondary"></i>
                                </div>
                                <h4 class="card-title mb-3">Technicians</h4>
                                <p class="card-text text-muted mb-4">
                                    <strong>Access:</strong> Equipment management, technical tools, and system maintenance.
                                </p>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-sign-in-alt me-2"></i>Login as Technician',
                                    ['prefix' => 'Technician', 'controller' => 'Login', 'action' => 'login'],
                                    ['class' => 'btn btn-secondary btn-lg w-100', 'escape' => false]
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Our MEG Services</h2>
                <p class="lead text-muted">
                    Comprehensive magnetoencephalography solutions for advanced brain monitoring and research.
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mb-4">
                        <i class="fas fa-brain text-primary"></i>
                    </div>
                    <h4 class="mb-3">Brain Activity Monitoring</h4>
                    <p class="text-muted">
                        Real-time monitoring of brain electrical activity with high temporal resolution.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mb-4">
                        <i class="fas fa-chart-line text-success"></i>
                    </div>
                    <h4 class="mb-3">Data Analysis</h4>
                    <p class="text-muted">
                        Advanced algorithms for processing and analyzing complex neurological data.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="service-card text-center">
                    <div class="service-icon mb-4">
                        <i class="fas fa-shield-alt text-info"></i>
                    </div>
                    <h4 class="mb-3">Secure Platform</h4>
                    <p class="text-muted">
                        HIPAA-compliant security with role-based access controls and data encryption.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
