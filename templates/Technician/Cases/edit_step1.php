<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 * @var array $patients
 * @var array $caseData
 * @var object $currentHospital
 */
$this->assign('title', 'Edit Case #' . $case->id . ' - Step 1: Patient Information');
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Progress Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-user-injured fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-primary">Step 1</strong>
                                <div class="small text-muted">Patient Info</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-procedures fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-muted">Step 2</strong>
                                <div class="small text-muted">Department & Procedures</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-clipboard-check fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-muted">Step 3</strong>
                                <div class="small text-muted">Review & Notes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1 Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-injured me-2"></i>
                        Patient Information
                    </h5>
                </div>
                <div class="card-body">
                    <form id="step1Form" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">
                                    Patient <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <select 
                                        name="patient_id" 
                                        id="patient_id" 
                                        class="form-select" 
                                        required
                                    >
                                        <option value="">-- Select Patient --</option>
                                        <?php foreach ($patients as $id => $name): ?>
                                            <option value="<?= h($id) ?>" <?= (!empty($caseData['patient_id']) && $caseData['patient_id'] == $id) ? 'selected' : '' ?>>
                                                <?= h($name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-plus"></i>',
                                        ['prefix' => 'Technician', 'controller' => 'Patients', 'action' => 'add'],
                                        [
                                            'class' => 'btn btn-outline-primary',
                                            'escape' => false,
                                            'title' => 'Add New Patient',
                                            'target' => '_blank'
                                        ]
                                    ) ?>
                                </div>
                                <div class="invalid-feedback">
                                    Please select a patient.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">
                                    Case Date <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    name="date" 
                                    id="date" 
                                    class="form-control" 
                                    value="<?= h($caseData['date'] ?? date('Y-m-d')) ?>"
                                    required
                                >
                                <div class="invalid-feedback">
                                    Please select a case date.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="symptoms" class="form-label">
                                Symptoms <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                name="symptoms" 
                                id="symptoms" 
                                class="form-control" 
                                rows="6"
                                placeholder="Describe the patient's symptoms in detail..."
                                required
                            ><?= h($caseData['symptoms'] ?? '') ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Provide detailed symptoms to help with AI-powered recommendations.
                            </div>
                            <div class="invalid-feedback">
                                Please enter the patient's symptoms.
                            </div>
                        </div>

                        <div id="alertContainer"></div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <?= $this->Html->link(
                                '<i class="fas fa-arrow-left me-2"></i>Cancel',
                                ['action' => 'index'],
                                ['class' => 'btn btn-outline-secondary', 'escape' => false]
                            ) ?>
                            
                            <button type="submit" class="btn btn-primary btn-lg" id="nextStepBtn">
                                <span id="btnText">
                                    Analyze & Continue <i class="fas fa-arrow-right ms-2"></i>
                                </span>
                                <span id="btnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow-sm mt-4 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="fas fa-robot me-2"></i>AI-Powered Assistance
                    </h6>
                    <p class="card-text small mb-0">
                        Our AI will analyze the patient's symptoms, age, and gender to automatically recommend:
                    </p>
                    <ul class="small mb-0 mt-2">
                        <li>Relevant exams and procedures</li>
                        <li>Appropriate medical department</li>
                        <li>Recommended sedation level</li>
                        <li>Case priority level</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('step1Form');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const alertContainer = document.getElementById('alertContainer');

    // Bootstrap validation
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        // Remove previous validation states
        form.classList.remove('was-validated');
        alertContainer.innerHTML = '';

        // Check form validity
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Disable button and show loading
        nextStepBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        try {
            // Get form data
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Send AJAX request to save step 1
            const response = await fetch('<?= $this->Url->build(['action' => 'saveEditStep1', $case->id]) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to step 2
                window.location.href = '<?= $this->Url->build(['action' => 'editStep2', $case->id]) ?>';
            } else {
                // Show errors
                let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0">';
                if (result.errors) {
                    for (const [field, message] of Object.entries(result.errors)) {
                        errorHtml += `<li>${message}</li>`;
                    }
                } else if (result.error) {
                    errorHtml += `<li>${result.error}</li>`;
                }
                errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                alertContainer.innerHTML = errorHtml;

                // Re-enable button
                nextStepBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    An error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Re-enable button
            nextStepBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
        }
    });

    // Real-time validation feedback
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (form.classList.contains('was-validated')) {
                this.classList.toggle('is-invalid', !this.checkValidity());
                this.classList.toggle('is-valid', this.checkValidity());
            }
        });

        field.addEventListener('input', function() {
            if (form.classList.contains('was-validated')) {
                this.classList.toggle('is-invalid', !this.checkValidity());
                this.classList.toggle('is-valid', this.checkValidity());
            }
        });
    });
});
</script>
