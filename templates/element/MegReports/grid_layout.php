<?php
/**
 * Renders the body of a custom grid layout slide (slide viewer and thumbnails).
 * Keep markup in sync with renderGridPreview() in templates/Doctor/MegReports/custom_layout.php.
 *
 * @var \App\View\AppView $this
 * @var array $layout Layout from SlideGridLayoutService (boxes may carry 'image_url')
 * @var bool $compact Thumbnail mode
 */
$compact = $compact ?? false;
$this->Html->css('meg-grid-layout', ['block' => true]);
?>
<div class="grid-layout<?= $compact ? ' compact' : '' ?>">
    <?php foreach ($layout['rows'] ?? [] as $row): ?>
        <?php
        $columns = $row['columns'] ?? [];
        $hasHeaders = (bool)array_filter(array_column($columns, 'header'));
        $widths = implode(' ', array_map(fn($column) => (float)($column['width'] ?? 1) . 'fr', $columns));
        ?>
        <div class="grid-layout-row<?= $hasHeaders ? ' has-headers' : '' ?>" style="flex: <?= (float)($row['height'] ?? 1) ?> 1 0; grid-template-columns: <?= h($widths) ?>;">
            <?php foreach ($columns as $column): ?>
                <div class="grid-layout-cell">
                    <?php if ($hasHeaders): ?>
                        <div class="grid-layout-header"><?= h($column['header'] ?? '') ?></div>
                    <?php endif; ?>
                    <div class="grid-layout-stack">
                        <?php foreach ($column['boxes'] ?? [] as $box): ?>
                            <?php
                            $type = $box['type'] ?? 'image';
                            $text = trim((string)($box['text'] ?? ''));
                            ?>
                            <div class="grid-layout-box">
                                <?php if (!empty($box['header'])): ?>
                                    <div class="grid-layout-header"><?= h($box['header']) ?></div>
                                <?php endif; ?>
                                <div class="grid-layout-body">
                                    <?php if ($type === 'image' && !empty($box['image_url'])): ?>
                                        <img src="<?= h($box['image_url']) ?>" alt="<?= h($box['header'] ?: ($column['header'] ?? '')) ?>" loading="lazy">
                                    <?php elseif ($type === 'image'): ?>
                                        <div class="grid-layout-empty"><i class="fas fa-image"></i><?= $compact ? '' : ' No image' ?></div>
                                    <?php elseif ($text === ''): ?>
                                        <div class="grid-layout-empty"><i class="fas fa-font"></i><?= $compact ? '' : ' No text' ?></div>
                                    <?php elseif ($type === 'bullets'): ?>
                                        <div class="grid-layout-text">
                                            <ul>
                                                <?php foreach (preg_split('/\r\n|\r|\n/', $text) as $line): ?>
                                                    <?php if (trim($line) !== ''): ?>
                                                        <li><?= h(trim($line)) ?></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <div class="grid-layout-text plain<?= $type === 'heading' ? ' heading' : '' ?>"><?= h($text) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
