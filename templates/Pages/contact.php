<?php
/**
 * Contact Page Template
 * 
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Contact MEG Healthcare');
?>

<!-- Contact Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
            <p class="lead text-muted">
                Get in touch with our team for inquiries, support, or collaboration opportunities.
            </p>
        </div>
    </div>
</section>

<!-- Contact Information -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Info -->
            <div class="col-lg-6">
                <h2 class="h3 mb-4">Get In Touch</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="contact-icon me-3">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Address</h5>
                                <p class="text-muted mb-0">123 Medical Center Drive<br>Healthcare District, HC 12345</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="contact-icon me-3">
                                <i class="fas fa-phone text-success"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Phone</h5>
                                <p class="text-muted mb-0">
                                    Main: +1 (555) 123-4567<br>
                                    Support: +1 (555) 123-HELP
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="contact-icon me-3">
                                <i class="fas fa-envelope text-info"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Email</h5>
                                <p class="text-muted mb-0">
                                    General: info@meghealthcare.com<br>
                                    Support: support@meghealthcare.com
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="contact-icon me-3">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Hours</h5>
                                <p class="text-muted mb-0">
                                    Monday - Friday: 8:00 AM - 6:00 PM<br>
                                    Saturday: 9:00 AM - 2:00 PM<br>
                                    Sunday: Emergency support only
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-4">Send Us a Message</h3>
                        <?php echo $this->Form->create(null, [
                            'id' => 'contactForm',
                            'class' => 'contact-form'
                        ]) ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <?php echo $this->Form->control('first_name', [
                                        'label' => 'First Name',
                                        'class' => 'form-control',
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo $this->Form->control('last_name', [
                                        'label' => 'Last Name',
                                        'class' => 'form-control',
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?php echo $this->Form->control('email', [
                                        'type' => 'email',
                                        'label' => 'Email',
                                        'class' => 'form-control',
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?php echo $this->Form->control('organization', [
                                        'label' => 'Organization',
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?php echo $this->Form->control('subject', [
                                        'type' => 'select',
                                        'label' => 'Subject',
                                        'class' => 'form-select',
                                        'options' => [
                                            '' => 'Choose a subject...',
                                            'general' => 'General Inquiry',
                                            'clinical' => 'Clinical Services',
                                            'research' => 'Research Collaboration',
                                            'technical' => 'Technical Support',
                                            'training' => 'Training Programs'
                                        ],
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?php echo $this->Form->control('message', [
                                        'type' => 'textarea',
                                        'label' => 'Message',
                                        'class' => 'form-control',
                                        'rows' => 4,
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?php echo $this->Form->button(
                                        '<i class="fas fa-paper-plane me-2"></i>Send Message',
                                        [
                                            'type' => 'submit',
                                            'class' => 'btn btn-primary btn-lg w-100',
                                            'escape' => false
                                        ]
                                    ) ?>
                                </div>
                            </div>
                        <?php echo $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Simple form handling
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Thank you for your message! We will get back to you soon.');
    this.reset();
});
</script>