<?php
/**
 * MEG PowerPoint report cover page heading (cover page is generated from the case; all other
 * slides are doctor-built custom layouts, see PPT_GRID_PRESETS)
 */
define('PPT_COVER_TITLE', 'Magnetoencephalography Report (MEG)');

/**
 * Custom Grid Layout Presets (slide_type 'custom_layout')
 *
 * Doctors pick one of these from the layout matrix, then choose per box (cell):
 * an optional header and a content type (image / text / bullets / heading).
 *
 * Each preset: 'group' = section in the picker.
 * Each row:
 *   'widths'  = relative column widths
 *   'stacks'  = boxes stacked inside each column (optional, default 1 per column)
 *   'types'   = default content type per box, in order (left to right, top to bottom in a column)
 *   'height'  = relative row height
 *   'headers' = true to prompt for a header above each column
 * Add a new layout by adding an entry here.
 */
define('PPT_GRID_PRESETS', serialize([
    // ---------- Basic grids ----------
    '1col' => [
        'name' => 'Single Box',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [1], 'types' => ['image']],
        ],
    ],
    '2col' => [
        'name' => '2 Columns',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'types' => ['image', 'image']],
        ],
    ],
    '3col' => [
        'name' => '3 Columns',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1, 1], 'types' => ['image', 'image', 'image']],
        ],
    ],
    '4col' => [
        'name' => '4 Columns',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1, 1, 1], 'types' => ['image', 'image', 'image', 'image']],
        ],
    ],
    '30_70' => [
        'name' => 'Narrow + Wide',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [3, 7], 'types' => ['bullets', 'image']],
        ],
    ],
    '70_30' => [
        'name' => 'Wide + Narrow',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [7, 3], 'types' => ['image', 'bullets']],
        ],
    ],
    '2x2' => [
        'name' => '2 x 2 Grid',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'types' => ['image', 'image']],
            ['height' => 1, 'widths' => [1, 1], 'types' => ['image', 'image']],
        ],
    ],
    '2x3' => [
        'name' => '2 x 3 Grid',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1, 1], 'types' => ['image', 'image', 'image']],
            ['height' => 1, 'widths' => [1, 1, 1], 'types' => ['image', 'image', 'image']],
        ],
    ],
    '1_over_2' => [
        'name' => '1 Wide + 2 Below',
        'group' => 'Basic',
        'rows' => [
            ['height' => 1, 'widths' => [1], 'types' => ['image']],
            ['height' => 1, 'widths' => [1, 1], 'types' => ['image', 'image']],
        ],
    ],

    // ---------- Each column with its own header ----------
    'hdr_1col' => [
        'name' => 'Single Box + Header',
        'group' => 'Columns with headers',
        'rows' => [
            ['height' => 1, 'widths' => [1], 'types' => ['image'], 'headers' => true],
        ],
    ],
    'hdr_2col' => [
        'name' => '2 Columns + Headers',
        'group' => 'Columns with headers',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'types' => ['image', 'image'], 'headers' => true],
        ],
    ],
    'hdr_3col' => [
        'name' => '3 Columns + Headers',
        'group' => 'Columns with headers',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1, 1], 'types' => ['image', 'image', 'image'], 'headers' => true],
        ],
    ],
    'hdr_4col' => [
        'name' => '4 Columns + Headers',
        'group' => 'Columns with headers',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1, 1, 1], 'types' => ['image', 'image', 'image', 'image'], 'headers' => true],
        ],
    ],
    'hdr_5col' => [
        'name' => '5 Columns + Headers',
        'group' => 'Columns with headers',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1, 1, 1, 1], 'types' => ['image', 'image', 'image', 'image', 'image'], 'headers' => true],
        ],
    ],
    'hdr_2x2' => [
        'name' => '2 x 2 + Headers',
        'group' => 'Columns with headers',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'types' => ['image', 'image'], 'headers' => true],
            ['height' => 1, 'widths' => [1, 1], 'types' => ['image', 'image'], 'headers' => true],
        ],
    ],

    // ---------- Columns with 2 boxes stacked ----------
    'stack_text_2img' => [
        'name' => 'Text + 2 Stacked Images',
        'group' => 'Stacked columns',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'stacks' => [1, 2], 'types' => ['bullets', 'image', 'image']],
        ],
    ],
    'stack_img_2img' => [
        'name' => 'Image + 2 Stacked',
        'group' => 'Stacked columns',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'stacks' => [1, 2], 'types' => ['image', 'image', 'image']],
        ],
    ],
    'stack_2img_img' => [
        'name' => '2 Stacked + Image',
        'group' => 'Stacked columns',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'stacks' => [2, 1], 'types' => ['image', 'image', 'image']],
        ],
    ],
    'stack_text_2img_hdr' => [
        'name' => 'Text + 2 Stacked + Headers',
        'group' => 'Stacked columns',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'stacks' => [1, 2], 'types' => ['bullets', 'image', 'image'], 'headers' => true],
        ],
    ],
    'stack_2col_hdr' => [
        'name' => '2 Columns x 2 Stacked + Headers',
        'group' => 'Stacked columns',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1], 'stacks' => [2, 2], 'types' => ['image', 'image', 'image', 'image'], 'headers' => true],
        ],
    ],
    'stack_3col_hdr' => [
        'name' => '3 Columns x 2 Stacked + Headers',
        'group' => 'Stacked columns',
        'rows' => [
            ['height' => 1, 'widths' => [1, 1, 1], 'stacks' => [2, 2, 2], 'types' => ['image', 'image', 'image', 'image', 'image', 'image'], 'headers' => true],
        ],
    ],

    // ---------- Main heading / text band above the boxes ----------
    'heading_1col' => [
        'name' => 'Main Heading + Box',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 0.5, 'widths' => [1], 'types' => ['heading']],
            ['height' => 4, 'widths' => [1], 'types' => ['image'], 'headers' => true],
        ],
    ],
    'heading_2col' => [
        'name' => 'Main Heading + 2 Columns',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 0.5, 'widths' => [1], 'types' => ['heading']],
            ['height' => 4, 'widths' => [1, 1], 'types' => ['image', 'image'], 'headers' => true],
        ],
    ],
    'heading_3col' => [
        'name' => 'Main Heading + 3 Columns',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 0.5, 'widths' => [1], 'types' => ['heading']],
            ['height' => 4, 'widths' => [1, 1, 1], 'types' => ['image', 'image', 'image'], 'headers' => true],
        ],
    ],
    'heading_4col' => [
        'name' => 'Main Heading + 4 Columns',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 0.5, 'widths' => [1], 'types' => ['heading']],
            ['height' => 4, 'widths' => [1, 1, 1, 1], 'types' => ['image', 'image', 'image', 'image'], 'headers' => true],
        ],
    ],
    'heading_stack_2col' => [
        'name' => 'Main Heading + 2 Columns x 2 Stacked',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 0.5, 'widths' => [1], 'types' => ['heading']],
            ['height' => 4, 'widths' => [1, 1], 'stacks' => [2, 2], 'types' => ['image', 'image', 'image', 'image'], 'headers' => true],
        ],
    ],
    'text_1col' => [
        'name' => 'Text + Single Box',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 1, 'widths' => [1], 'types' => ['bullets']],
            ['height' => 3, 'widths' => [1], 'types' => ['image']],
        ],
    ],
    'text_2col' => [
        'name' => 'Text + 2 Columns',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 1, 'widths' => [1], 'types' => ['bullets']],
            ['height' => 3, 'widths' => [1, 1], 'types' => ['image', 'image']],
        ],
    ],
    'text_3col' => [
        'name' => 'Text + 3 Columns',
        'group' => 'Main heading or text on top',
        'rows' => [
            ['height' => 1, 'widths' => [1], 'types' => ['bullets']],
            ['height' => 3, 'widths' => [1, 1, 1], 'types' => ['image', 'image', 'image']],
        ],
    ],
]));

/**
 * PPT Styling Configuration
 * 
 * Font Sizes (in points - larger values for better readability):
 * - Page heading: 36pt (was 24pt)
 * - Content/body: 24pt (was 22pt) 
 * - Cover footer: 18pt
 * - Legends/column headers: 18pt
 * 
 * Note: PowerPoint slides need larger fonts than documents
 */
define('PPT_STYLES', serialize([
    'slide' => [
        'width' => 960,
        'height' => 540,
        'background_color' => 'FFFFFF',
        'margin' => 20,
        'top_margin' => 10,
    ],
    'title' => [
        'font_family' => 'Calibri',
        'font_size' => 24,
        'font_color' => '000000',
        'font_bold' => false,
        'height' => 30,
        'margin_bottom' => 15,
    ],
    'subtitle' => [
        'font_family' => 'Calibri',
        'font_size' => 20,
        'font_color' => '000000',
        'font_bold' => false,
        'bullet' => true,
        'height' => 26,
        'margin_bottom' => 12,
    ],
    'column_header' => [
        'font_family' => 'Calibri',
        'font_size' => 14,
        'font_color' => '000000',
        'font_bold' => false,
        'alignment' => 'left',
        'margin_bottom' => 8,
    ],
    'content' => [
        'font_family' => 'Calibri',
        'font_size' => 14,
        'font_color' => '333333',
        'line_height' => 1.3,
    ],
    // Cover page specific styles
    'cover' => [
        'heading_font_size' => 24,
        'content_font_size' => 14,
        'footer_font_size' => 12,
        'patient_name_bold' => true,
    ],
]));

