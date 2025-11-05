<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Report $report
 */
$reportData = json_decode($report->report_data, true) ?? [];
$reportContent = $reportData['content'] ?? '';

$this->assign('title', 'Report Preview #' . $report->id);
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4 no-print">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-eye me-2"></i>Report Preview #<?php echo  h($report->id) ?>
                    </h2>
                    <p class="mb-0">
                        <?php if (isset($report->case->patient_user)): ?>
                            <i class="fas fa-user-injured me-2"></i><?php echo  $this->PatientMask->displayName($report->case->patient_user) ?>
                        <?php endif; ?>
                        <?php if (isset($report->hospital)): ?>
                            <span class="ms-3"><i class="fas fa-hospital me-2"></i><?php echo  h($report->hospital->name) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-download me-1"></i>PDF',
                            ['action' => 'download', $report->id, 'pdf'],
                            ['class' => 'btn btn-light', 'escape' => false, 'target' => '_blank']
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit',
                            ['action' => 'edit', $report->id],
                            ['class' => 'btn btn-light', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'view', $report->id],
                            ['class' => 'btn btn-outline-light', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Print Preview Content -->
        <div class="col-lg-8">
            <!-- Report Preview Card -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3 no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-medical-alt me-2 text-primary"></i>MEG Report Preview
                        </h5>
                    </div>
                </div>
                <div class="card-body bg-white p-0">
                    <!-- Printable Report Content -->
                    <div id="printableReport" class="p-5" style="font-family: 'Times New Roman', serif; line-height: 1.6; min-height: 600px; background: white;">
                        <?php if ($reportContent): ?>
                            <?php echo  $reportContent ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-medical-alt fa-4x text-muted mb-3"></i>
                                <h3 class="text-muted">No Report Content Available</h3>
                                <p class="text-muted">Please edit the report to add content before previewing.</p>
                                <?php echo  $this->Html->link(
                                    '<i class="fas fa-edit me-2"></i>Edit Report',
                                    ['action' => 'edit', $report->id],
                                    ['class' => 'btn btn-primary', 'escape' => false]
                                ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 no-print">
            <!-- Quick Actions -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>Edit Report',
                            ['action' => 'edit', $report->id],
                            ['class' => 'btn btn-warning d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-eye me-2"></i>View Details',
                            ['action' => 'view', $report->id],
                            ['class' => 'btn btn-info d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-medical me-2"></i>View Case',
                            ['controller' => 'Cases', 'action' => 'view', $report->case_id],
                            ['class' => 'btn btn-outline-primary d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>

            <!-- Report Information -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Report Information
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold">Report ID:</td>
                            <td><span class="badge bg-primary"><?php echo  h($report->id) ?></span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Case ID:</td>
                            <td>
                                <?php echo  $this->Html->link(
                                    '#' . $report->case_id,
                                    ['controller' => 'Cases', 'action' => 'view', $report->case_id],
                                    ['class' => 'text-decoration-none']
                                ) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Patient:</td>
                            <td>
                                <?php if (isset($report->case->patient_user)): ?>
                                    <?php echo  $this->PatientMask->displayName($report->case->patient_user) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Status:</td>
                            <td>
                                <span class="badge bg-<?php echo  $report->status === 'approved' ? 'success' : ($report->status === 'reviewed' ? 'warning' : 'secondary') ?>">
                                    <?php echo  h(ucfirst($report->status)) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Created:</td>
                            <td><?php echo  $report->created->format('M j, Y') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Download Options -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-download me-2 text-primary"></i>Download Options
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-pdf me-2"></i>PDF Format',
                            ['action' => 'download', $report->id, 'pdf'],
                            ['class' => 'btn btn-outline-danger d-flex align-items-center justify-content-center', 'escape' => false, 'target' => '_blank']
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-word me-2"></i>Word Document',
                            ['action' => 'download', $report->id, 'docx'],
                            ['class' => 'btn btn-outline-primary d-flex align-items-center justify-content-center', 'escape' => false, 'target' => '_blank']
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-code me-2"></i>HTML Format',
                            ['action' => 'download', $report->id, 'html'],
                            ['class' => 'btn btn-outline-success d-flex align-items-center justify-content-center', 'escape' => false, 'target' => '_blank']
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-alt me-2"></i>Plain Text',
                            ['action' => 'download', $report->id, 'txt'],
                            ['class' => 'btn btn-outline-secondary d-flex align-items-center justify-content-center', 'escape' => false, 'target' => '_blank']
                        ); ?>
                    </div>
                    
                    <div class="mt-3 p-2 bg-light rounded">
                        <div class="small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Tip:</strong> Use PDF for printing, Word for editing, HTML for web sharing.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        font-family: 'Times New Roman', serif !important;
        font-size: 12pt;
        line-height: 1.4;
        color: black !important;
        background: white !important;
    }
    
    #printableReport {
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        background: white !important;
        font-family: 'Times New Roman', serif !important;
        line-height: 1.6 !important;
        color: black !important;
    }
    
    h1, h2, h3, h4, h5, h6 {
        page-break-after: avoid;
        color: black !important;
    }
    
    p {
        orphans: 3;
        widows: 3;
    }
    
    table {
        page-break-inside: avoid;
        border-collapse: collapse;
    }
    
    .page-break {
        page-break-before: always;
    }
    
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .row, .col-lg-8 {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .card, .card-body {
        border: none !important;
        box-shadow: none !important;
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}

/* Screen-only styling for better preview */
@media screen {
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    
    .table-borderless td {
        border: none;
        padding: 0.5rem 0.75rem;
    }
    
    .fw-semibold {
        font-weight: 600;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    .badge {
        font-weight: 500;
        border-radius: 6px;
    }
}
</style>

<script>
// Enhanced print functionality
function printReport() {
    window.print();
}

// Auto-focus and enhance user experience
document.addEventListener('DOMContentLoaded', function() {
    // Add keyboard shortcut for print (Ctrl+P)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
    
    // Scroll to top when page loads
    window.scrollTo(0, 0);
    
    // Add loading indicator for downloads
    const downloadLinks = document.querySelectorAll('a[href*="download"]');
    downloadLinks.forEach(link => {
        link.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Preparing...';
            this.classList.add('disabled');
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('disabled');
            }, 3000);
        });
    });
});
</script>
