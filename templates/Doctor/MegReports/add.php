<?php
/**
 * Add slide: choose one of the doctor's saved layouts or a layout from the matrix
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Report $report
 * @var int $reportId
 * @var array $gridPresets
 * @var iterable<\App\Model\Entity\SlideLayoutTemplate> $layoutTemplates
 */
use App\Service\SlideGridLayoutService;

$this->assign('title', 'Add Slide');
$this->Html->css('meg-grid-layout', ['block' => true]);
?>

<style>
.category-section {
    margin-bottom: 25px;
}
.category-title {
    font-size: 14px;
    font-weight: bold;
    color: #6c757d;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Slide
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Case ID: <?= h($report->case_id ?? 'N/A') ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?= $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Slides',
                        ['action' => 'index', $reportId],
                        ['class' => 'btn btn-light', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-dark">
                <span class="badge bg-danger me-2">1</span>Choose a Layout
            </h5>
        </div>
        <div class="card-body p-4">
            <?php if (count($layoutTemplates) > 0): ?>
                <div class="category-section">
                    <div class="category-title">
                        <i class="fas fa-star me-2"></i>My Layouts
                    </div>
                    <div class="layout-matrix mb-3">
                        <?php foreach ($layoutTemplates as $layoutTemplate): ?>
                            <div class="layout-option position-relative">
                                <a href="<?= $this->Url->build(['action' => 'add', '?' => ['report_id' => $reportId, 'slide_type' => SlideGridLayoutService::SLIDE_TYPE, 'template_id' => $layoutTemplate->id]]) ?>"
                                   class="text-decoration-none text-dark d-block" title="<?= h($layoutTemplate->title ?: $layoutTemplate->name) ?>">
                                    <?= $this->element('MegReports/layout_diagram', ['rows' => $layoutTemplate->getLayoutData()['rows']]) ?>
                                    <div class="layout-option-name"><?= h($layoutTemplate->name) ?></div>
                                </a>
                                <?= $this->Form->postLink(
                                    '<i class="fas fa-times"></i>',
                                    ['action' => 'deleteLayoutTemplate', $layoutTemplate->id, '?' => ['report_id' => $reportId]],
                                    [
                                        'escape' => false,
                                        'class' => 'btn btn-sm btn-light position-absolute top-0 end-0 m-1 py-0 px-1',
                                        'title' => 'Delete saved layout',
                                        'confirm' => 'Delete saved layout "' . $layoutTemplate->name . '"? Slides already made from it are not affected.',
                                    ]
                                ) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="category-section mb-0">
                <div class="category-title">
                    <i class="fas fa-th-large me-2"></i>Layouts
                    <small class="text-muted fw-normal ms-2">Pick a grid, then choose a header and image / text for each box</small>
                </div>
                <?php foreach (SlideGridLayoutService::groupedPresets() as $groupName => $groupPresets): ?>
                    <div class="layout-group-title"><?= h($groupName) ?></div>
                    <div class="layout-matrix mb-3">
                        <?php foreach ($groupPresets as $presetKey => $preset): ?>
                            <a href="<?= $this->Url->build(['action' => 'add', '?' => ['report_id' => $reportId, 'slide_type' => SlideGridLayoutService::SLIDE_TYPE, 'preset' => $presetKey]]) ?>"
                               class="layout-option" title="<?= h($preset['name']) ?>">
                                <?= $this->element('MegReports/layout_diagram', ['rows' => $preset['rows']]) ?>
                                <div class="layout-option-name"><?= h($preset['name']) ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
