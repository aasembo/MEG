<?php
/**
 * Add / edit a custom grid layout slide
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ReportSlide $slide
 * @var \App\Model\Entity\Report $report
 * @var int $reportId
 * @var array $layout Current layout (boxes carry 'image_url')
 * @var array $gridPresets PPT_GRID_PRESETS
 * @var array $cellTypes SlideGridLayoutService::CELL_TYPES
 * @var bool $isNew
 */
use App\Service\SlideGridLayoutService;

$this->assign('title', $isNew ? 'Add Custom Slide' : 'Edit Custom Slide');
$this->Html->css('meg-grid-layout', ['block' => true]);

$initialBoxes = array_map(fn($box) => [
    'header' => $box['header'] ?? '',
    'type' => $box['type'] ?? 'image',
    'text' => $box['text'] ?? '',
    'image_url' => $box['image_url'] ?? null,
], SlideGridLayoutService::cells($layout));
$initialColumns = array_map(fn($column) => ['header' => $column['header'] ?? ''], SlideGridLayoutService::columns($layout));
?>

<style>
.column-editor {
    border-left: 3px solid #dc3545;
}
.box-editor {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}
.cell-editor.is-active {
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.35);
}
.cell-editor .current-image {
    max-height: 90px;
    max-width: 100%;
    object-fit: contain;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}
.custom-preview-wrap {
    position: sticky;
    top: 20px;
}
.custom-preview {
    aspect-ratio: 16 / 9;
    background: #fff;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    padding: 2.5% 2%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.custom-preview-title {
    font-size: 13px;
    line-height: 1.25;
    margin-bottom: 4px;
    color: #000;
    overflow-wrap: anywhere;
}
.custom-preview-subtitle {
    font-size: 11px;
    margin-bottom: 4px;
    color: #000;
}
.custom-preview .grid-layout {
    flex: 1;
    min-height: 0;
    gap: 5px;
}
.custom-preview .grid-layout-row {
    column-gap: 8px;
}
.custom-preview .grid-layout-header {
    font-size: 8px;
    padding-bottom: 3px;
}
.custom-preview .grid-layout-text {
    font-size: 7.5px;
}
.custom-preview .grid-layout-text.heading {
    font-size: 10.5px;
}
.custom-preview .grid-layout-empty {
    font-size: 9px;
}
.layout-matrix.compact-matrix {
    grid-template-columns: repeat(auto-fill, minmax(92px, 1fr));
    gap: 8px;
}
.layout-matrix.compact-matrix .layout-option-name {
    font-size: 10px;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-th-large me-2"></i><?= $isNew ? 'Add Custom Layout Slide' : 'Edit Custom Layout Slide' ?>
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

    <?= $this->Form->create($slide, [
        'type' => 'file',
        'id' => 'customSlideForm',
        'url' => $isNew
            ? ['action' => 'add', '?' => ['report_id' => $reportId, 'slide_type' => SlideGridLayoutService::SLIDE_TYPE]]
            : ['action' => 'edit', $slide->id],
    ]) ?>
    <?= $this->Form->hidden('preset', ['value' => $layout['preset'], 'id' => 'presetInput']) ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <!-- Layout -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <span class="badge bg-danger me-2">1</span>Layout
                    </h5>
                </div>
                <div class="card-body">
                    <div id="layoutMatrix">
                        <?php foreach (SlideGridLayoutService::groupedPresets() as $groupName => $groupPresets): ?>
                            <div class="layout-group-title"><?= h($groupName) ?></div>
                            <div class="layout-matrix compact-matrix">
                                <?php foreach ($groupPresets as $presetKey => $preset): ?>
                                    <button type="button" class="layout-option<?= $presetKey === $layout['preset'] ? ' active' : '' ?>"
                                            data-preset="<?= h($presetKey) ?>" title="<?= h($preset['name']) ?>">
                                        <?= $this->element('MegReports/layout_diagram', ['rows' => $preset['rows']]) ?>
                                        <div class="layout-option-name"><?= h($preset['name']) ?></div>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Switching layout keeps what you entered: box 1 stays box 1, column 1 stays column 1, and so on.
                        Boxes that no longer fit are removed when you save.
                    </small>
                </div>
            </div>

            <!-- Title -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <span class="badge bg-danger me-2">2</span>Title
                    </h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->control('title', [
                        'label' => 'Slide Title',
                        'class' => 'form-control',
                        'id' => 'slideTitle',
                        'placeholder' => 'e.g. MEG source localization',
                    ]) ?>
                    <div class="mt-3">
                        <?= $this->Form->control('subtitle', [
                            'label' => 'Subtitle (optional, shown as a bullet under the title)',
                            'class' => 'form-control',
                            'id' => 'slideSubtitle',
                        ]) ?>
                    </div>
                </div>
            </div>

            <!-- Boxes -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <span class="badge bg-danger me-2">3</span>Columns &amp; Boxes
                    </h5>
                    <small class="text-muted">Click a box, then paste a screenshot (Cmd+V / Ctrl+V) to put it there.</small>
                </div>
                <div class="card-body" id="cellEditors"></div>
                <div id="parkedEditors" style="display: none;"></div>
            </div>

            <!-- Save -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="save_as_template" value="1" id="saveAsTemplate">
                        <label class="form-check-label" for="saveAsTemplate">
                            Also save this layout to <strong>My Layouts</strong> (layout, headers and text, without images)
                        </label>
                    </div>
                    <div id="templateNameGroup" class="mb-3" style="display: none;">
                        <input type="text" name="template_name" class="form-control" maxlength="150" placeholder="Name for this layout, e.g. Motor mapping 3-up">
                    </div>
                    <div class="d-flex gap-2">
                        <?= $this->Form->button('<i class="fas fa-save me-2"></i>' . ($isNew ? 'Add Slide' : 'Save Slide'), [
                            'class' => 'btn btn-danger',
                            'escapeTitle' => false,
                        ]) ?>
                        <?= $this->Html->link('Cancel', ['action' => 'index', $reportId], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live preview -->
        <div class="col-lg-5">
            <div class="custom-preview-wrap">
                <h6 class="fw-bold text-muted mb-2"><i class="fas fa-eye me-2"></i>Preview</h6>
                <div class="custom-preview">
                    <div class="custom-preview-title" id="previewTitle"></div>
                    <div class="custom-preview-subtitle" id="previewSubtitle"></div>
                    <div id="previewGrid" style="flex: 1; min-height: 0; display: flex;"></div>
                </div>
                <small class="text-muted d-block mt-2">The downloaded PowerPoint uses the same proportions.</small>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<?php $this->start('script'); ?>
<script>
(function () {
    const PRESETS = <?= json_encode($gridPresets, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const CELL_TYPES = <?= json_encode($cellTypes, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const initialBoxes = <?= json_encode($initialBoxes, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const initialColumns = <?= json_encode($initialColumns, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    const presetInput = document.getElementById('presetInput');
    const editorsEl = document.getElementById('cellEditors');
    const parkingEl = document.getElementById('parkedEditors');
    const boxEditors = [];    // flat box index -> editor
    const columnEditors = []; // flat column index -> editor
    let activeIndex = null;

    function el(tag, attrs, children) {
        const node = document.createElement(tag);
        Object.entries(attrs || {}).forEach(([key, value]) => {
            if (key === 'text') node.textContent = value;
            else if (key === 'className') node.className = value;
            else node.setAttribute(key, value);
        });
        (children || []).forEach(child => child && node.appendChild(child));
        return node;
    }

    function setEnabled(root, enabled) {
        root.querySelectorAll('input, textarea').forEach(input => input.disabled = !enabled);
    }

    // Expand a preset into rows -> columns -> boxes with flat indices (same order as SlideGridLayoutService::build)
    function presetShape(key) {
        let boxIndex = 0;
        let columnIndex = 0;
        return PRESETS[key].rows.map((row, r) => {
            let typeIndex = 0;
            const columns = row.widths.map((width, c) => {
                const stack = (row.stacks && row.stacks[c]) || 1;
                const boxes = [];
                for (let k = 0; k < stack; k++) {
                    boxes.push({index: boxIndex++, type: row.types[typeIndex++] || 'image', stackIndex: k, stackCount: stack});
                }
                return {index: columnIndex++, row: r, col: c, width: width, boxes: boxes};
            });
            return {height: row.height || 1, headers: !!row.headers, columns: columns};
        });
    }

    function createColumnEditor(ci, initial) {
        const header = el('input', {type: 'text', className: 'form-control form-control-sm', name: `columns[${ci}][header]`, maxlength: '255'});
        header.value = initial.header || '';
        const title = el('strong');
        const boxesWrap = el('div');
        const root = el('div', {className: 'card column-editor mb-3'}, [
            el('div', {className: 'card-body'}, [
                el('div', {className: 'mb-2'}, [title]),
                el('label', {className: 'form-label small text-muted mb-1', text: 'Column header'}),
                el('div', {className: 'mb-3'}, [header]),
                boxesWrap,
            ]),
        ]);
        header.addEventListener('input', refresh);
        return {root, header, title, boxesWrap};
    }

    function createBoxEditor(i, initial) {
        const name = field => `cells[${i}][${field}]`;
        const idBase = `cell${i}`;

        const typeGroup = el('div', {className: 'btn-group btn-group-sm mb-2', role: 'group'});
        Object.entries(CELL_TYPES).forEach(([value, label]) => {
            const id = `${idBase}_type_${value}`;
            typeGroup.appendChild(el('input', {type: 'radio', className: 'btn-check', name: name('type'), id: id, value: value, autocomplete: 'off'}));
            typeGroup.appendChild(el('label', {className: 'btn btn-outline-danger', for: id, text: label}));
        });

        const header = el('input', {type: 'text', className: 'form-control form-control-sm', name: name('header'), placeholder: 'Box header (optional)', maxlength: '255'});
        header.value = initial.header || '';
        const headerWrap = el('div', {className: 'mb-2'}, [header]);

        const currentImg = el('img', {className: 'current-image me-3', alt: 'Current image'});
        const removeBox = el('input', {type: 'checkbox', className: 'form-check-input', name: name('remove_image'), value: '1', id: `${idBase}_remove`});
        const currentWrap = el('div', {className: 'd-flex align-items-center mb-2'}, [
            currentImg,
            el('div', {className: 'form-check'}, [removeBox, el('label', {className: 'form-check-label small', for: `${idBase}_remove`, text: 'Remove image'})]),
        ]);
        const fileInput = el('input', {type: 'file', className: 'form-control form-control-sm', name: name('image_file'), accept: 'image/jpeg,image/png,image/gif'});
        const imageSection = el('div', {}, [currentWrap, fileInput]);

        const textArea = el('textarea', {className: 'form-control form-control-sm', name: name('text'), rows: '3'});
        textArea.value = initial.text || '';
        const textSection = el('div', {}, [textArea]);

        const label = el('div', {className: 'small fw-bold mb-2'});
        const root = el('div', {className: 'cell-editor box-editor p-2 mb-2 rounded', 'data-index': i}, [label, headerWrap, typeGroup, imageSection, textSection]);

        const editor = {root, label, header, headerWrap, typeGroup, fileInput, removeBox, currentImg, currentWrap, imageSection, textArea, textSection,
            existingUrl: initial.image_url || null, objectUrl: null, touched: !!(initial.header || initial.text || initial.image_url)};

        setType(editor, initial.type || 'image');

        root.addEventListener('input', () => { editor.touched = true; refresh(); });
        root.addEventListener('change', () => { editor.touched = true; refresh(); });
        root.addEventListener('focusin', () => setActive(i));
        root.addEventListener('click', () => setActive(i));
        typeGroup.addEventListener('change', () => setType(editor, getType(editor)));
        fileInput.addEventListener('change', () => {
            if (editor.objectUrl) URL.revokeObjectURL(editor.objectUrl);
            editor.objectUrl = fileInput.files[0] ? URL.createObjectURL(fileInput.files[0]) : null;
            if (fileInput.files[0]) removeBox.checked = false;
            refresh();
        });

        return editor;
    }

    function getType(editor) {
        const checked = editor.typeGroup.querySelector('input:checked');
        return checked ? checked.value : 'image';
    }

    function setType(editor, type) {
        editor.typeGroup.querySelector(`input[value="${type}"]`).checked = true;
        editor.imageSection.style.display = type === 'image' ? '' : 'none';
        editor.textSection.style.display = type === 'image' ? 'none' : '';
        editor.textArea.placeholder = {bullets: 'One bullet point per line', heading: 'Main heading'}[type] || 'Text';
        refresh();
    }

    function imageSrc(editor) {
        if (editor.objectUrl) return editor.objectUrl;
        if (editor.existingUrl && !editor.removeBox.checked) return editor.existingUrl;
        return null;
    }

    function setActive(i) {
        activeIndex = i;
        boxEditors.forEach((editor, j) => editor && editor.root.classList.toggle('is-active', j === i));
    }

    function applyPreset(key) {
        presetInput.value = key;
        document.querySelectorAll('#layoutMatrix .layout-option').forEach(btn => btn.classList.toggle('active', btn.dataset.preset === key));

        const shape = presetShape(key);
        let columnCount = 0;
        let boxCount = 0;

        shape.forEach((row, r) => {
            row.columns.forEach(column => {
                const ci = column.index;
                if (!columnEditors[ci]) columnEditors[ci] = createColumnEditor(ci, initialColumns[ci] || {});
                const colEditor = columnEditors[ci];
                colEditor.title.textContent = shape.length > 1
                    ? `Row ${r + 1}, column ${column.col + 1}`
                    : `Column ${column.col + 1} of ${row.columns.length}`;
                colEditor.header.placeholder = row.headers ? 'e.g. Left hemisphere' : 'Optional';
                editorsEl.appendChild(colEditor.root);
                setEnabled(colEditor.root, true);
                columnCount++;

                column.boxes.forEach(box => {
                    const i = box.index;
                    if (!boxEditors[i]) {
                        boxEditors[i] = createBoxEditor(i, initialBoxes[i] || {type: box.type});
                    } else if (!boxEditors[i].touched) {
                        setType(boxEditors[i], box.type);
                    }
                    const editor = boxEditors[i];
                    const stacked = box.stackCount > 1;
                    editor.label.textContent = stacked
                        ? `Box ${i + 1} - ${box.stackIndex === 0 ? 'top' : (box.stackIndex === box.stackCount - 1 ? 'bottom' : 'middle')}`
                        : `Box ${i + 1}`;
                    // Box headers only apply to stacked boxes; a single box uses the column header
                    editor.headerWrap.style.display = stacked ? '' : 'none';
                    colEditor.boxesWrap.appendChild(editor.root);
                    setEnabled(editor.root, true);
                    editor.header.disabled = !stacked;
                    boxCount++;
                });
            });
        });

        // Columns/boxes beyond this layout are kept (so switching back restores them) but not submitted
        columnEditors.forEach((editor, ci) => {
            if (editor && ci >= columnCount) { parkingEl.appendChild(editor.root); setEnabled(editor.root, false); }
        });
        boxEditors.forEach((editor, i) => {
            if (editor && i >= boxCount) { parkingEl.appendChild(editor.root); setEnabled(editor.root, false); }
        });
        if (activeIndex !== null && activeIndex >= boxCount) setActive(null);

        refresh();
    }

    function renderBoxBody(editor) {
        const body = el('div', {className: 'grid-layout-body'});
        const type = getType(editor);
        const text = editor.textArea.value.trim();
        if (type === 'image') {
            const src = imageSrc(editor);
            body.appendChild(src
                ? el('img', {src: src, alt: ''})
                : el('div', {className: 'grid-layout-empty'}, [el('i', {className: 'fas fa-image'}), document.createTextNode(' Image')]));
        } else if (text === '') {
            body.appendChild(el('div', {className: 'grid-layout-empty'}, [el('i', {className: 'fas fa-font'}), document.createTextNode(' Text')]));
        } else if (type === 'bullets') {
            const list = el('ul');
            text.split(/\r\n|\r|\n/).filter(line => line.trim() !== '').forEach(line => list.appendChild(el('li', {text: line.trim()})));
            body.appendChild(el('div', {className: 'grid-layout-text'}, [list]));
        } else {
            body.appendChild(el('div', {className: 'grid-layout-text plain' + (type === 'heading' ? ' heading' : ''), text: text}));
        }
        return body;
    }

    // Same markup as templates/element/MegReports/grid_layout.php
    function renderGridPreview(shape) {
        const grid = el('div', {className: 'grid-layout'});
        shape.forEach(row => {
            const hasHeaders = row.headers || row.columns.some(column => columnEditors[column.index].header.value.trim() !== '');
            const rowEl = el('div', {className: 'grid-layout-row' + (hasHeaders ? ' has-headers' : '')});
            rowEl.style.flex = `${row.height} 1 0`;
            rowEl.style.gridTemplateColumns = row.columns.map(column => `${column.width}fr`).join(' ');

            row.columns.forEach(column => {
                const cellEl = el('div', {className: 'grid-layout-cell'});
                if (hasHeaders) {
                    const headerText = columnEditors[column.index].header.value.trim();
                    // Empty header slots on header layouts show a hint here only; nothing is exported for them
                    cellEl.appendChild(headerText || !row.headers
                        ? el('div', {className: 'grid-layout-header', text: headerText})
                        : el('div', {className: 'grid-layout-header placeholder', text: 'Column header'}));
                }
                const stackEl = el('div', {className: 'grid-layout-stack'});
                column.boxes.forEach(box => {
                    const editor = boxEditors[box.index];
                    const boxEl = el('div', {className: 'grid-layout-box'});
                    const boxHeader = box.stackCount > 1 ? editor.header.value.trim() : '';
                    if (boxHeader) boxEl.appendChild(el('div', {className: 'grid-layout-header', text: boxHeader}));
                    boxEl.appendChild(renderBoxBody(editor));
                    stackEl.appendChild(boxEl);
                });
                cellEl.appendChild(stackEl);
                rowEl.appendChild(cellEl);
            });
            grid.appendChild(rowEl);
        });
        document.getElementById('previewGrid').replaceChildren(grid);
    }

    function refresh() {
        if (!presetInput.value) return;
        const shape = presetShape(presetInput.value);
        const ready = shape.every(row => row.columns.every(column =>
            columnEditors[column.index] && column.boxes.every(box => boxEditors[box.index])));
        if (!ready) return;

        boxEditors.forEach(editor => {
            if (!editor) return;
            editor.currentWrap.style.display = editor.existingUrl ? '' : 'none';
            if (editor.existingUrl) {
                editor.currentImg.src = editor.existingUrl;
                editor.currentImg.style.opacity = editor.removeBox.checked ? '0.3' : '1';
            }
        });

        const title = document.getElementById('slideTitle').value.trim();
        const subtitle = document.getElementById('slideSubtitle').value.trim();
        document.getElementById('previewTitle').textContent = title || 'Slide title';
        const subtitleEl = document.getElementById('previewSubtitle');
        subtitleEl.textContent = subtitle ? '• ' + subtitle : '';
        subtitleEl.style.display = subtitle ? '' : 'none';

        renderGridPreview(shape);
    }

    document.getElementById('layoutMatrix').addEventListener('click', e => {
        const btn = e.target.closest('.layout-option');
        if (btn) applyPreset(btn.dataset.preset);
    });
    document.getElementById('slideTitle').addEventListener('input', refresh);
    document.getElementById('slideSubtitle').addEventListener('input', refresh);
    document.getElementById('saveAsTemplate').addEventListener('change', function () {
        document.getElementById('templateNameGroup').style.display = this.checked ? '' : 'none';
    });

    // Paste a screenshot into the selected box
    document.addEventListener('paste', e => {
        if (activeIndex === null || !boxEditors[activeIndex]) return;
        const item = Array.from(e.clipboardData?.items || []).find(it => it.type.indexOf('image') !== -1);
        if (!item) return;
        e.preventDefault();

        const blob = item.getAsFile();
        const ext = (blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
        const file = new File([blob], `pasted_${Date.now()}.${ext}`, {type: blob.type});
        const editor = boxEditors[activeIndex];
        const transfer = new DataTransfer();
        transfer.items.add(file);
        editor.fileInput.files = transfer.files;
        setType(editor, 'image');
        editor.fileInput.dispatchEvent(new Event('change', {bubbles: true}));
    });

    applyPreset(presetInput.value);
})();
</script>
<?php $this->end(); ?>
