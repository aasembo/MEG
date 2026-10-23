<?php
/**
 * Small schematic of a grid layout for the layout matrix picker.
 * Accepts either PPT_GRID_PRESETS rows ('widths'/'stacks'/'types') or stored layout rows ('columns' -> 'boxes').
 * Rows with column headers show a bar above each column.
 *
 * @var \App\View\AppView $this
 * @var array $rows Layout rows
 */
$this->Html->css('meg-grid-layout', ['block' => true]);
?>
<div class="layout-diagram">
    <?php foreach ($rows as $row): ?>
        <?php
        if (isset($row['columns'])) {
            $columns = $row['columns'];
        } else {
            // Expand a preset row into columns of typed boxes
            $columns = [];
            $typeIndex = 0;
            foreach ($row['widths'] as $col => $width) {
                $boxes = [];
                for ($k = 0, $stack = (int)($row['stacks'][$col] ?? 1); $k < $stack; $k++) {
                    $boxes[] = ['type' => $row['types'][$typeIndex++] ?? 'image'];
                }
                $columns[] = ['width' => $width, 'boxes' => $boxes];
            }
        }
        $widths = implode(' ', array_map(fn($column) => (float)($column['width'] ?? 1) . 'fr', $columns));
        $hasHeaders = !empty($row['headers']) || array_filter(array_column($columns, 'header'));
        ?>
        <div class="layout-diagram-row<?= $hasHeaders ? ' has-headers' : '' ?>" style="flex: <?= (float)($row['height'] ?? 1) ?> 1 0; grid-template-columns: <?= h($widths) ?>;">
            <?php foreach ($columns as $column): ?>
                <div class="layout-diagram-column">
                    <?php foreach ($column['boxes'] ?? [] as $box): ?>
                        <div class="layout-diagram-cell type-<?= h($box['type'] ?? 'image') ?>"></div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
