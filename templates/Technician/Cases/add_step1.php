<?php
/**
 * @var \App\View\AppView $this
 * @var array $patients
 * @var array $caseData
 * @var object $currentHospital
 */
$this->assign('title', 'Add New Case - Step 1: Patient Information');
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Case
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-user-injured me-2"></i>Step 1: Patient Information
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?= $this->Html->link(
                        '<i class="fas fa-list me-2"></i>All Cases',
                        ['action' => 'index'],
                        ['class' => 'btn btn-light', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Progress Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-primary">Step 1</strong>
                                <div class="small text-muted">Patient Info</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2 my-0">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-procedures"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-muted">Step 2</strong>
                                <div class="small text-muted">Department & Procedures</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2 my-0">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-muted">Step 3</strong>
                                <div class="small text-muted">Review & Notes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Information Form -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-user-injured me-2 text-primary"></i>
                        Patient Information
                    </h6>
                </div>
                <div class="card-body">
                    <form id="step1Form" novalidate>
                        <div class="row g-4">
                            <!-- Patient Selection -->
                            <div class="col-12">
                                <label for="patient_id" class="form-label fw-semibold">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    Select Patient
                                </label>
                                <select name="patient_id" id="patient_id" class="form-select form-select-lg" required>
                                    <option value="">Choose a patient...</option>
                                    <?php foreach ($patients as $id => $name): ?>
                                        <option value="<?= h($id) ?>" <?= (isset($caseData['patient_id']) && $caseData['patient_id'] == $id) ? 'selected' : '' ?>>
                                            <?= h($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a patient.
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Search for the patient by name. The display shows Name (Gender/Age) format.
                                </div>
                            </div>

                            <!-- Case Date -->
                            <div class="col-md-6">
                                <label for="date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar me-2 text-primary"></i>
                                    Case Date
                                </label>
                                <input 
                                    type="date" 
                                    name="date" 
                                    id="date" 
                                    class="form-control form-control-lg" 
                                    value="<?= h($caseData['date'] ?? date('Y-m-d')) ?>"
                                    required
                                >
                                <div class="invalid-feedback">
                                    Please select a valid case date.
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Date when the case/examination will take place.
                                </div>
                            </div>

                            <!-- Case Time -->
                            <div class="col-md-6">
                                <label for="time" class="form-label fw-semibold">
                                    <i class="fas fa-clock me-2 text-primary"></i>
                                    Case Time
                                </label>
                                <input 
                                    type="time" 
                                    name="time" 
                                    id="time" 
                                    class="form-control form-control-lg" 
                                    value="<?= h($caseData['time'] ?? '') ?>"
                                >
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Optional: Scheduled time for the case.
                                </div>
                            </div>

                            <!-- Symptoms -->
                            <div class="col-12">
                                <label for="symptoms" class="form-label fw-semibold">
                                    <i class="fas fa-notes-medical me-2 text-primary"></i>
                                    Patient Symptoms
                                </label>
                                <textarea 
                                    name="symptoms" 
                                    id="symptoms" 
                                    class="form-control form-control-lg" 
                                    rows="4"
                                    placeholder="Describe the patient's symptoms, complaints, or reason for examination..."
                                    required
                                ><?= h($caseData['symptoms'] ?? '') ?></textarea>
                                <div class="invalid-feedback">
                                    Please describe the patient's symptoms.
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Detailed description of symptoms will help with procedure recommendations.
                                </div>
                            </div>
                        </div>

                        <div id="alertContainer" class="mt-4"></div>

                        <div class="d-flex justify-content-between align-items-center pt-4 border-top mt-4">
                            <?= $this->Html->link(
                                '<i class="fas fa-times me-2"></i>Cancel',
                                ['action' => 'index'],
                                ['class' => 'btn btn-outline-secondary btn-lg', 'escape' => false]
                            ) ?>
                            
                            <button type="submit" class="btn btn-primary btn-lg" id="nextStepBtn">
                                <span id="btnText">
                                    Next Step
                                    <i class="fas fa-arrow-right ms-2"></i>
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

            <!-- Information Alert -->
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3 fa-lg"></i>
                    <div>
                        <h6 class="mb-1">Step 1 of 3</h6>
                        <div class="small mb-0">
                            Please select a patient and provide basic case information. This information will be used to recommend appropriate procedures in the next step.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('step1Form');
    const patientSelect = document.getElementById('patient_id');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const alertContainer = document.getElementById('alertContainer');

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        // Clear previous alerts
        alertContainer.innerHTML = '';

        // Validate form
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
            const response = await fetch('<?= $this->Url->build(['action' => 'saveStep1']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                alertContainer.innerHTML = `
                    <div class="alert alert-success border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 fa-lg"></i>
                            <div>
                                <strong>Step 1 Complete!</strong> Proceeding to Department & Procedures selection...
                            </div>
                        </div>
                    </div>
                `;

                // Redirect to step 2 after short delay
                setTimeout(() => {
                    window.location.href = '<?= $this->Url->build(['action' => 'addStep2']) ?>';
                }, 1000);
            } else {
                // Show errors
                let errorHtml = '<div class="alert alert-danger border-0" role="alert">';
                errorHtml += '<div class="d-flex align-items-start">';
                errorHtml += '<i class="fas fa-exclamation-circle me-2 fa-lg mt-1"></i>';
                errorHtml += '<div class="flex-grow-1">';
                errorHtml += '<h6 class="mb-1">Validation Errors</h6>';
                errorHtml += '<ul class="mb-0">';
                
                if (result.errors) {
                    if (typeof result.errors === 'object') {
                        for (const [field, messages] of Object.entries(result.errors)) {
                            if (typeof messages === 'object') {
                                for (const [key, message] of Object.entries(messages)) {
                                    errorHtml += `<li>${field}: ${message}</li>`;
                                }
                            } else {
                                errorHtml += `<li>${field}: ${messages}</li>`;
                            }
                        }
                    } else {
                        errorHtml += `<li>${result.errors}</li>`;
                    }
                } else if (result.error) {
                    errorHtml += `<li>${result.error}</li>`;
                } else {
                    errorHtml += '<li>An error occurred while saving step 1.</li>';
                }
                
                errorHtml += '</ul></div></div></div>';
                alertContainer.innerHTML = errorHtml;

                // Re-enable button
                nextStepBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');

                // Scroll to errors
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } catch (error) {
            console.error('Error:', error);
            alertContainer.innerHTML = `
                <div class="alert alert-danger border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
                        <div>
                            <strong>Network Error:</strong> Please check your connection and try again.
                        </div>
                    </div>
                </div>
            `;
            
            // Re-enable button
            nextStepBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');

            // Scroll to errors
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
