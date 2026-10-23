<?php
declare(strict_types=1);

namespace App\Service;

use App\Lib\S3DocumentService;

/**
 * Grid layouts for doctor-defined MEG report slides (slide_type 'custom_layout')
 *
 * A layout is rows -> columns -> stacked boxes, stored as JSON on report_slides.layout_data:
 *   {
 *     "preset": "hdr_2col_stack2",
 *     "rows": [
 *       {"height": 1, "headers": true, "columns": [
 *         {"width": 1, "header": "Left hemisphere", "boxes": [
 *           {"header": "", "type": "image", "text": "", "image_path": "..."},
 *           {"header": "", "type": "image", "text": "", "image_path": null}
 *         ]}, ...
 *       ]}
 *     ]
 *   }
 *
 * - Column header: shown above the column (aligned across the row).
 * - Box header: only for columns with 2+ stacked boxes, shown above that box.
 *
 * Boxes and columns are addressed by flat index in reading order (row by row, left to right,
 * top to bottom inside a column), so content survives switching between presets.
 */
class SlideGridLayoutService
{
    public const SLIDE_TYPE = 'custom_layout';

    public const CELL_TYPES = [
        'image' => 'Image',
        'text' => 'Text',
        'bullets' => 'Bullet points',
        'heading' => 'Heading',
    ];

    /**
     * Get available presets from PPT_GRID_PRESETS
     *
     * @return array
     */
    public static function presets(): array
    {
        return unserialize(PPT_GRID_PRESETS);
    }

    /**
     * Presets grouped for the layout picker: [group name => [preset key => preset]]
     *
     * @return array
     */
    public static function groupedPresets(): array
    {
        $groups = [];
        foreach (self::presets() as $key => $preset) {
            $groups[$preset['group'] ?? 'Layouts'][$key] = $preset;
        }

        return $groups;
    }

    /**
     * Decode stored layout JSON
     *
     * @param string|null $json Stored layout_data
     * @return array Layout with 'preset' and 'rows'
     */
    public static function decode(?string $json): array
    {
        $data = $json ? json_decode($json, true) : null;
        if (!is_array($data) || empty($data['rows']) || !is_array($data['rows'])) {
            return ['preset' => null, 'rows' => []];
        }

        return $data;
    }

    /**
     * Build an empty layout from a preset
     *
     * @param string $presetKey Preset key
     * @return array|null Null if preset is unknown
     */
    public static function fromPreset(string $presetKey): ?array
    {
        return self::build($presetKey, [], []);
    }

    /**
     * Number of boxes and columns a preset defines
     *
     * @param string $presetKey Preset key
     * @return array{boxes: int, columns: int}
     */
    public static function counts(string $presetKey): array
    {
        $boxes = 0;
        $columns = 0;
        foreach (self::presets()[$presetKey]['rows'] ?? [] as $row) {
            foreach (array_keys($row['widths']) as $col) {
                $columns++;
                $boxes += (int)($row['stacks'][$col] ?? 1);
            }
        }

        return ['boxes' => $boxes, 'columns' => $columns];
    }

    /**
     * Build a layout from a preset plus per-box and per-column values (keyed by flat index)
     *
     * @param string $presetKey Preset key
     * @param array $boxInput [box index => ['header', 'type', 'text', 'image_path']]
     * @param array $columnInput [column index => ['header']]
     * @return array|null Null if preset is unknown
     */
    public static function build(string $presetKey, array $boxInput, array $columnInput): ?array
    {
        $preset = self::presets()[$presetKey] ?? null;
        if (!$preset) {
            return null;
        }

        $rows = [];
        $boxIndex = 0;
        $columnIndex = 0;
        foreach ($preset['rows'] as $presetRow) {
            $columns = [];
            $rowBoxIndex = 0; // index into the row's 'types'
            foreach ($presetRow['widths'] as $col => $width) {
                $stack = max(1, (int)($presetRow['stacks'][$col] ?? 1));
                $boxes = [];
                for ($k = 0; $k < $stack; $k++) {
                    $input = $boxInput[$boxIndex] ?? [];
                    $type = (string)($input['type'] ?? '');
                    if (!isset(self::CELL_TYPES[$type])) {
                        $type = $presetRow['types'][$rowBoxIndex] ?? 'image';
                    }

                    $boxes[] = [
                        // Box headers only apply inside stacked columns; single boxes use the column header
                        'header' => $stack > 1 ? trim((string)($input['header'] ?? '')) : '',
                        'type' => $type,
                        'text' => trim((string)($input['text'] ?? '')),
                        'image_path' => $input['image_path'] ?? null,
                    ];
                    $boxIndex++;
                    $rowBoxIndex++;
                }

                $columns[] = [
                    'width' => $width,
                    'header' => trim((string)($columnInput[$columnIndex]['header'] ?? '')),
                    'boxes' => $boxes,
                ];
                $columnIndex++;
            }

            $rows[] = [
                'height' => $presetRow['height'] ?? 1,
                'headers' => !empty($presetRow['headers']),
                'columns' => $columns,
            ];
        }

        return ['preset' => $presetKey, 'rows' => $rows];
    }

    /**
     * Flatten layout boxes in reading order
     *
     * @param array $layout Layout
     * @return array
     */
    public static function cells(array $layout): array
    {
        $boxes = [];
        foreach (self::columns($layout) as $column) {
            foreach ($column['boxes'] ?? [] as $box) {
                $boxes[] = $box;
            }
        }

        return $boxes;
    }

    /**
     * Flatten layout columns in reading order
     *
     * @param array $layout Layout
     * @return array
     */
    public static function columns(array $layout): array
    {
        $columns = [];
        foreach ($layout['rows'] ?? [] as $row) {
            foreach ($row['columns'] ?? [] as $column) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * All image paths referenced by a layout
     *
     * @param array $layout Layout
     * @return array<string>
     */
    public static function imagePaths(array $layout): array
    {
        return array_values(array_filter(array_column(self::cells($layout), 'image_path')));
    }

    /**
     * Widest row's column count (stored in report_slides.layout_columns)
     *
     * @param array $layout Layout
     * @return int
     */
    public static function maxColumns(array $layout): int
    {
        $max = 1;
        foreach ($layout['rows'] ?? [] as $row) {
            $max = max($max, count($row['columns'] ?? []));
        }

        return $max;
    }

    /**
     * Apply a callback to every box (by reference)
     *
     * @param array $layout Layout
     * @param callable $callback fn(array &$box, int $index)
     * @return array
     */
    private static function mapBoxes(array $layout, callable $callback): array
    {
        $index = 0;
        foreach ($layout['rows'] as &$row) {
            foreach ($row['columns'] as &$column) {
                foreach ($column['boxes'] as &$box) {
                    $callback($box, $index++);
                }
            }
        }

        return $layout;
    }

    /**
     * Strip images from a layout (for saving as a reusable template)
     *
     * @param array $layout Layout
     * @return array
     */
    public static function withoutImages(array $layout): array
    {
        return self::mapBoxes($layout, function (array &$box) {
            $box['image_path'] = null;
        });
    }

    /**
     * Add signed 'image_url' to every box that has an image
     *
     * @param array $layout Layout
     * @param \App\Lib\S3DocumentService $s3Service S3 service
     * @return array
     */
    public static function withImageUrls(array $layout, S3DocumentService $s3Service): array
    {
        return self::mapBoxes($layout, function (array &$box) use ($s3Service) {
            $box['image_url'] = !empty($box['image_path']) ? $s3Service->getDownloadUrl($box['image_path']) : null;
        });
    }

    /**
     * Put an image into a box (switching it to an image box)
     *
     * @param array $layout Layout (modified in place)
     * @param int $index Flat box index
     * @param string $imagePath New S3 path
     * @return string|null Previous image path of that box, if any
     */
    public static function setCellImage(array &$layout, int $index, string $imagePath): ?string
    {
        $oldPath = null;
        $layout = self::mapBoxes($layout, function (array &$box, int $i) use ($index, $imagePath, &$oldPath) {
            if ($i === $index) {
                $oldPath = $box['image_path'] ?? null;
                $box['type'] = 'image';
                $box['image_path'] = $imagePath;
            }
        });

        return $oldPath;
    }

    /**
     * Human labels for every box, e.g. "Box 2 - Left hemisphere (bottom)"
     *
     * @param array $layout Layout
     * @return array<string>
     */
    public static function cellLabels(array $layout): array
    {
        $labels = [];
        $index = 0;
        foreach (self::columns($layout) as $column) {
            $stack = count($column['boxes'] ?? []);
            foreach ($column['boxes'] ?? [] as $k => $box) {
                $label = 'Box ' . ($index + 1);
                $name = $box['header'] ?: ($column['header'] ?? '');
                if ($name !== '') {
                    $label .= ' - ' . $name;
                }
                if ($stack > 1) {
                    $label .= ' (' . ($k === 0 ? 'top' : ($k === $stack - 1 ? 'bottom' : 'middle')) . ')';
                }
                $labels[] = $label;
                $index++;
            }
        }

        return $labels;
    }
}
