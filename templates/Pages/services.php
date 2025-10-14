<?php
/**
 * Services Page Template
 * 
 * @var \App\View\AppView $this
 */
$this->assign('title', 'MEG Healthcare Services');
?>

<!-- Services Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 fw-bold mb-4">Our MEG Services</h1>
            <p class="lead">
                Comprehensive magnetoencephalography solutions for clinical diagnosis, 
                research, and therapeutic applications.
            </p>
        </div>
    </div>
</section>

<!-- Services Grid -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Clinical MEG -->
            <div class="col-lg-6">
                <div class="service-card h-100 p-4 border rounded shadow-sm">
                    <div class="d-flex align-items-start">
                        <div class="service-icon me-4">
                            <i class="fas fa-stethoscope text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-3">Clinical MEG</h3>
                            <p class="text-muted mb-3">
                                Pre-surgical brain mapping, epilepsy localization, and functional 
                                brain assessment for clinical decision-making.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Epilepsy source localization</li>
                                <li><i class="fas fa-check text-success me-2"></i>Pre-surgical planning</li>
                                <li><i class="fas fa-check text-success me-2"></i>Functional brain mapping</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Research MEG -->
            <div class="col-lg-6">
                <div class="service-card h-100 p-4 border rounded shadow-sm">
                    <div class="d-flex align-items-start">
                        <div class="service-icon me-4">
                            <i class="fas fa-microscope text-info" style="font-size: 2.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-3">Research MEG</h3>
                            <p class="text-muted mb-3">
                                Advanced neuroscience research capabilities for cognitive studies, 
                                brain development, and neurological disorders.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Cognitive neuroscience studies</li>
                                <li><i class="fas fa-check text-success me-2"></i>Brain development research</li>
                                <li><i class="fas fa-check text-success me-2"></i>Neuroplasticity studies</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Analysis -->
            <div class="col-lg-6">
                <div class="service-card h-100 p-4 border rounded shadow-sm">
                    <div class="d-flex align-items-start">
                        <div class="service-icon me-4">
                            <i class="fas fa-chart-line text-success" style="font-size: 2.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-3">Data Analysis</h3>
                            <p class="text-muted mb-3">
                                Sophisticated data processing and analysis services with 
                                custom algorithms and visualization tools.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Custom analysis pipelines</li>
                                <li><i class="fas fa-check text-success me-2"></i>Statistical modeling</li>
                                <li><i class="fas fa-check text-success me-2"></i>Interactive visualization</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Training & Support -->
            <div class="col-lg-6">
                <div class="service-card h-100 p-4 border rounded shadow-sm">
                    <div class="d-flex align-items-start">
                        <div class="service-icon me-4">
                            <i class="fas fa-graduation-cap text-warning" style="font-size: 2.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-3">Training & Support</h3>
                            <p class="text-muted mb-3">
                                Comprehensive training programs and ongoing technical support 
                                for healthcare professionals and researchers.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>MEG operation training</li>
                                <li><i class="fas fa-check text-success me-2"></i>Data interpretation workshops</li>
                                <li><i class="fas fa-check text-success me-2"></i>24/7 technical support</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>