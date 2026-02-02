<?php
/**
 * MEG PowerPoint Report Pages Configuration
 * 
 * Layout Types:
 * - cover: Title page with patient demographics
 * - single_image: Full-width single image
 * - text_bullets: Text content with bullet points
 * - two_column_images: Two images side by side with headers
 * - text_and_image: Text on left, image on right (or vice versa)
 * - image_with_legend: Image with color-coded legend below
 * - text_only: Full text content (summary page)
 * 
 * Content Types for columns:
 * - 'image': Upload single image
 * - 'text': Editable text content
 * - 'composite_image': Single uploaded image containing multiple views
 */

define('PPT_REPORT_PAGES', serialize([

    // =====================================================
    // SLIDE 1: Cover Page / Patient Summary
    // =====================================================
    'cover_page' => [
        'order' => 1,
        'columns' => 1,
        'layout' => 'cover',
        'title' => 'Magnetoencephalography Report (MEG)',
        'editable' => false,
        'required' => true,
        'fields' => [
            'patient_name' => ['label' => 'Name', 'format' => 'Last, First', 'bold' => true],
            'date_of_birth' => ['label' => 'Date of Birth', 'format' => 'xx/xx/xxx'],
            'mrn_fin' => ['label' => 'MRN', 'format' => 'xxx; FIN: xxx'],
            'date_of_study' => ['label' => 'Date of Study', 'format' => 'xx/xx/xxx'],
            'referring_physician' => ['label' => 'Referring Physician'],
            'meg_id' => ['label' => 'MEG ID', 'format' => 'case_xxx'],
        ],
        'footer_fields' => [
            'sedation_status' => ['default' => 'MEG performed without sedation', 'editable' => true],
            'demographics' => ['format' => '{age} {gender} {handedness}'],
            'asms' => ['label' => 'ASMs', 'editable' => true],
        ],
    ],

    // =====================================================
    // SLIDE 2: Original EEG Signals - Text Description
    // =====================================================
    'original_eeg_signals_text' => [
        'order' => 2,
        'columns' => 1,
        'layout' => 'text_bullets',
        'title' => 'Original EEG Signals',
        'editable' => true,
        'required' => true,
        'col1' => [
            'type' => 'text',
            'format' => 'bullets',
            'subheading' => 'Interictal',
            'subheading_underline' => true,
            'placeholder' => '• Description of EEG findings...',
        ],
    ],

    // =====================================================
    // SLIDE 3: Original EEG Signals - Image
    // =====================================================
    'original_eeg_signals_image' => [
        'order' => 3,
        'columns' => 1,
        'layout' => 'single_image',
        'title' => 'Original EEG Signals',
        'editable' => true,
        'required' => true,
        'col1' => [
            'type' => 'image',
            'description' => 'EEG waveform image',
        ],
    ],

    // =====================================================
    // SLIDE 4: EEG/MEG Discharge
    // =====================================================
    'eeg_meg_discharge' => [
        'order' => 4,
        'columns' => 1,
        'layout' => 'single_image',
        'title' => 'EEG/MEG discharge',
        'editable' => true,
        'required' => true,
        'col1' => [
            'type' => 'composite_image',
            'description' => 'Composite image showing EEG, MEG Magnetometer, Gradiometers, and Sensor Contour Maps',
        ],
        'footer_text' => '[1-70Hz, 0.2 sec/div]',
        'footer_editable' => true,
    ],

    // =====================================================
    // SLIDE 5: MEG Source Localization - ECD
    // =====================================================
    'meg_source_localization_ecd' => [
        'order' => 5,
        'columns' => 1,
        'layout' => 'single_image',
        'title' => 'MEG source localization with equivalent current dipole (ECD).',
        'editable' => true,
        'required' => true,
        'col1' => [
            'type' => 'composite_image',
            'description' => 'Multi-view brain image (coronal, sagittal, axial, 3D)',
        ],
    ],

    // =====================================================
    // SLIDE 6: MEG Source Localization - sLORETA
    // =====================================================
    'meg_source_localization_loreta' => [
        'order' => 6,
        'columns' => 1,
        'layout' => 'single_image',
        'title' => 'MEG source localization with current density (sLORETA).',
        'editable' => true,
        'required' => true,
        'col1' => [
            'type' => 'composite_image',
            'description' => '3D brain with heat map and MRI slices',
        ],
    ],

    // =====================================================
    // SLIDE 7: Summary Localization - 4 View
    // =====================================================
    'summary_localization_discharge_4view' => [
        'order' => 7,
        'columns' => 1,
        'layout' => 'single_image',
        'title' => 'Summary localization of discharges (ECD)',
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'composite_image',
            'description' => '4-view summary (coronal, sagittal, axial, 3D)',
        ],
    ],

    // =====================================================
    // SLIDE 8: Summary Localization - Axial Grid
    // =====================================================
    'summary_localization_discharge_axial' => [
        'order' => 8,
        'columns' => 1,
        'layout' => 'single_image',
        'title' => 'Summary localization of discharges (ECD)',
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'description' => 'Axial slice grid view',
        ],
    ],

    // =====================================================
    // SLIDE 9: Summary Localization - Coronal Grid
    // =====================================================
    'summary_localization_discharge_coronal' => [
        'order' => 9,
        'columns' => 1,
        'layout' => 'single_image',
        'title' => 'Summary localization of discharges (ECD)',
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'description' => 'Coronal slice grid view',
        ],
    ],

    // =====================================================
    // SLIDE 10: Functional Mapping - Sensory (Median Nerve)
    // =====================================================
    'functional_mapping_sensory_median' => [
        'order' => 10,
        'columns' => 2,
        'layout' => 'two_column_images',
        'title' => 'Functional mapping',
        'subtitle' => 'Sensory mapping: median nerve stimulation',
        'subtitle_bullet' => true,
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'header' => '<b>Left hand</b> stimulation resulted in a SEF that was localized to the orthotopic contralateral sensory cortex',
            'header_editable' => true,
        ],
        'col2' => [
            'type' => 'image',
            'header' => '<b>Right hand</b> stimulation resulted in a SEF that was localized to the orthotopic contralateral sensory cortex',
            'header_editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 11: Functional Mapping - Sensory (Pneumatic)
    // =====================================================
    'functional_mapping_sensory_pneumatic' => [
        'order' => 11,
        'columns' => 2,
        'layout' => 'two_column_images',
        'title' => 'Functional mapping',
        'subtitle' => 'Sensory mapping: pneumatic stimulation (index finger)',
        'subtitle_bullet' => true,
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'header' => '<b>Left hand</b> stimulation resulted in a SEF that was localized to the orthotopic contralateral sensory cortex',
            'header_editable' => true,
        ],
        'col2' => [
            'type' => 'image',
            'header' => '<b>Right hand</b> stimulation resulted in a SEF that was localized to the orthotopic contralateral sensory cortex',
            'header_editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 12: Functional Mapping - Motor Mapping (Text + Image)
    // =====================================================
    'functional_mapping_motor' => [
        'order' => 12,
        'columns' => 2,
        'layout' => 'text_and_image',
        'title' => 'Functional mapping',
        'subtitle' => 'Motor mapping',
        'subtitle_bullet' => true,
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'text',
            'format' => 'bullets',
            'default_content' => "• We instructed the patient to spontaneously raise and lower their palm at a frequency of one movement per second to assess motor function by analyzing <b>theta</b> band power.\n• The top row shows the motor responses for the left hand, while the bottom row depicts responses for the right hand.\n• The motor function can be localized to the primary motor area, leading us to conclude that the patient's motor function is normal.",
        ],
        'col2' => [
            'type' => 'composite_image',
            'description' => 'Motor mapping results (2 rows: Left/Right)',
        ],
    ],

    // =====================================================
    // SLIDE 13: Functional Mapping - Motor (Finger Tapping)
    // =====================================================
    'functional_mapping_motor_finger' => [
        'order' => 13,
        'columns' => 2,
        'layout' => 'two_column_images',
        'title' => 'Functional mapping',
        'subtitle' => 'Motor mapping: Index finger tapping',
        'subtitle_bullet' => true,
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'header' => '<b>Left index finger</b> tapping resulted in a MEF that was localized to the orthotopic contralateral motor cortex',
            'header_editable' => true,
        ],
        'col2' => [
            'type' => 'image',
            'header' => '<b>Right index finger</b> tapping resulted in a MEF that was localized to the orthotopic contralateral motor cortex',
            'header_editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 14: Functional Mapping - Auditory (Binaural)
    // =====================================================
    'functional_mapping_auditory' => [
        'order' => 14,
        'columns' => 2,
        'layout' => 'two_column_images',
        'title' => 'Functional mapping',
        'subtitle' => 'Auditory mapping: Binaural pure tone',
        'subtitle_bullet' => true,
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'header' => '<b>Left ear</b> stimulation resulted in a AEF that was localized to the contralateral superior temporal gyrus.',
            'header_editable' => true,
        ],
        'col2' => [
            'type' => 'image',
            'header' => '<b>Right ear</b> stimulation resulted in a AEF that was localized to the contralateral superior temporal gyrus.',
            'header_editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 15: Functional Mapping - Language (Receptive)
    // =====================================================
    'functional_mapping_language' => [
        'order' => 15,
        'columns' => 2,
        'layout' => 'text_header_two_images',
        'title' => 'Functional mapping',
        'subtitle' => 'Receptive language mapping',
        'subtitle_bullet' => true,
        'editable' => true,
        'required' => false,
        'header_text' => [
            'type' => 'text',
            'format' => 'bullets_sub',
            'content' => [
                'MEG/EEG signals show engagement of receptive language cortex in both hemispheres but with preponderance of the left, suggestive of <b>left dominance for receptive language</b>.',
                'Note: Signal-to-noise ratio (SNR) of MEG/EEG signals were low due to *** *** ***',
            ],
        ],
        'col1' => [
            'type' => 'image',
        ],
        'col2' => [
            'type' => 'image',
        ],
    ],

    // =====================================================
    // SLIDE 16: Functional Mapping - Visual (Checkerboard)
    // =====================================================
    'functional_mapping_visual' => [
        'order' => 16,
        'columns' => 2,
        'layout' => 'two_column_images',
        'title' => 'Functional mapping',
        'subtitle' => 'Visual mapping: flashing checkerboard',
        'subtitle_bullet' => true,
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'header' => '<b>Left visual field</b> stimulation resulted in a VEF that was localized to the orthotopic contralateral visual cortex',
            'header_editable' => true,
        ],
        'col2' => [
            'type' => 'image',
            'header' => '<b>Right visual field</b> stimulation resulted in a VEF that was localized to the orthotopic contralateral visual cortex',
            'header_editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 17: MEG Source Localization Summary (3 legend items)
    // =====================================================
    'meg_localization_summary_3' => [
        'order' => 17,
        'columns' => 1,
        'layout' => 'image_with_legend',
        'title' => 'MEG source localization summary (ECD).',
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'description' => '3D brain with localization markers',
        ],
        'legend' => [
            'items' => [
                ['label' => 'Discharges', 'color' => '#FFFF00'],
                ['label' => 'Sensory', 'color' => '#0000FF'],
                ['label' => 'Language', 'color' => '#FF0000'],
            ],
            'editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 18: MEG Source Localization Summary (4 legend items)
    // =====================================================
    'meg_localization_summary_4' => [
        'order' => 18,
        'columns' => 1,
        'layout' => 'image_with_legend',
        'title' => 'MEG source localization summary (ECD).',
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'description' => '3D brain with localization markers',
        ],
        'legend' => [
            'items' => [
                ['label' => 'Discharges', 'color' => '#FFFF00'],
                ['label' => 'Sensory', 'color' => '#0000FF'],
                ['label' => 'AEF', 'color' => '#00FF00'],
                ['label' => 'Language', 'color' => '#FF0000'],
            ],
            'editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 19: MEG Source Localization Summary (5 legend items)
    // =====================================================
    'meg_localization_summary_5' => [
        'order' => 19,
        'columns' => 1,
        'layout' => 'image_with_legend',
        'title' => 'MEG source localization summary (ECD).',
        'editable' => true,
        'required' => false,
        'col1' => [
            'type' => 'image',
            'description' => '3D brain with localization markers',
        ],
        'legend' => [
            'items' => [
                ['label' => 'Discharges', 'color' => '#FFFF00'],
                ['label' => 'Sensory', 'color' => '#0000FF'],
                ['label' => 'AEF', 'color' => '#00FF00'],
                ['label' => 'Language', 'color' => '#FF0000'],
                ['label' => 'Visual', 'color' => '#800080'],
            ],
            'editable' => true,
        ],
    ],

    // =====================================================
    // SLIDE 20: Summary (Text Only)
    // =====================================================
    'summary' => [
        'order' => 20,
        'columns' => 1,
        'layout' => 'text_only',
        'title' => 'Summary',
        'editable' => true,
        'required' => true,
        'col1' => [
            'type' => 'text',
            'format' => 'structured_bullets',
        ],
        'default_sections' => [
            [
                'heading' => '(I) Epileptiform discharges',
                'items' => [
                    ['title' => 'Original EEG Signals', 'subitems' => ['***']],
                    ['title' => 'MEG source localization', 'subitems' => ['The equivalent current dipole sources corresponding to the sharp waves were localized to the *** ***.']],
                ],
            ],
            [
                'heading' => '(II) Functional mapping',
                'items' => [
                    ['title' => 'Sensory mapping', 'subitems' => [
                        'The localization of left and right sensory evoked fields suggest normal anatomic organization of primary sensory cortex bilaterally.',
                        'The localization of the *** sensory evoked field suggests normal anatomic organization of the *** primary sensory cortex.',
                        'The *** sensory evoked field was poorly localized due to low signal-to-noise ratio.',
                    ]],
                    ['title' => 'Auditory mapping', 'subitems' => [
                        'The localization of the auditory evoked field suggests normal anatomic organization of the primary auditory cortex.',
                        'The *** sensory evoked field was poorly localized due to low signal-to-noise ratio.',
                    ]],
                    ['title' => 'Motor mapping', 'subitems' => [
                        'The localization of left and right motor evoked fields suggest normal anatomic organization of primary motor cortex bilaterally.',
                        'The localization of the *** motor evoked field suggests normal anatomic organization of the *** primary motor cortex.',
                        'The *** motor evoked field was poorly localized due to low signal-to-noise ratio.',
                    ]],
                    ['title' => 'Language', 'subitems' => ['*** Left-hemisphere dominance for receptive language.']],
                ],
            ],
        ],
    ],
]));

/**
 * PPT Layout Templates Configuration
 */
define('PPT_LAYOUTS', serialize([
    'cover' => [
        'name' => 'Cover Page',
        'has_title' => true,
        'title_centered' => true,
        'title_bold' => true,
        'content_centered' => true,
    ],
    'single_image' => [
        'name' => 'Single Image',
        'has_title' => true,
        'title_centered' => true,
        'image_centered' => true,
        'max_image_width' => 850,
        'max_image_height' => 450,
    ],
    'text_bullets' => [
        'name' => 'Text with Bullets',
        'has_title' => true,
        'title_centered' => true,
        'content_left_aligned' => true,
    ],
    'two_column_images' => [
        'name' => 'Two Column Images',
        'has_title' => true,
        'has_subtitle' => true,
        'has_column_headers' => true,
        'column_gap' => 30,
        'max_image_width' => 420,
        'max_image_height' => 350,
    ],
    'text_and_image' => [
        'name' => 'Text and Image',
        'has_title' => true,
        'has_subtitle' => true,
        'col1_width_percent' => 30,
        'col2_width_percent' => 70,
    ],
    'text_header_two_images' => [
        'name' => 'Text Header with Two Images',
        'has_title' => true,
        'has_subtitle' => true,
        'header_text_area' => true,
        'column_gap' => 30,
    ],
    'image_with_legend' => [
        'name' => 'Image with Legend',
        'has_title' => true,
        'title_centered' => true,
        'legend_position' => 'bottom',
        'legend_horizontal' => true,
    ],
    'text_only' => [
        'name' => 'Text Only',
        'has_title' => true,
        'title_centered' => true,
        'title_large' => true,
        'content_left_aligned' => true,
    ],
]));

/**
 * PPT Styling Configuration
 */
define('PPT_STYLES', serialize([
    'slide' => [
        'width' => 960,
        'height' => 540,
        'background_color' => 'FFFFFF',
        'margin' => 20,
        'top_margin' => 15,
    ],
    'title' => [
        'font_family' => 'Calibri',
        'font_size' => 29,
        'font_color' => '000000',
        'font_bold' => true,
        'height' => 40,
        'margin_bottom' => 8,
    ],
    'subtitle' => [
        'font_family' => 'Calibri',
        'font_size' => 21,
        'font_color' => '000000',
        'font_bold' => false,
        'bullet' => true,
        'height' => 32,
        'margin_bottom' => 15,
    ],
    'column_header' => [
        'font_family' => 'Calibri',
        'font_size' => 15,
        'font_color' => '000000',
        'alignment' => 'left',
        'margin_bottom' => 10,
    ],
    'content' => [
        'font_family' => 'Calibri',
        'font_size' => 14,
        'font_color' => '333333',
        'line_height' => 1.4,
    ],
    'text_and_image_content' => [
        'font_family' => 'Calibri',
        'font_size' => 17,
        'font_color' => '000000',
        'line_height' => 1.4,
    ],
    'bullet' => [
        'font_size' => 14,
        'indent' => 20,
        'sub_indent' => 40,
    ],
    'legend' => [
        'font_size' => 12,
        'box_size' => 15,
        'spacing' => 30,
    ],
    'footer' => [
        'font_size' => 10,
        'font_color' => '666666',
    ],
]));

/**
 * Slide Categories for UI grouping
 */
define('PPT_SLIDE_CATEGORIES', serialize([
    'cover' => [
        'name' => 'Cover Page',
        'slides' => ['cover_page'],
    ],
    'eeg_signals' => [
        'name' => 'EEG Signals',
        'slides' => ['original_eeg_signals_text', 'original_eeg_signals_image'],
    ],
    'discharge' => [
        'name' => 'EEG/MEG Discharge',
        'slides' => ['eeg_meg_discharge'],
    ],
    'source_localization' => [
        'name' => 'Source Localization',
        'slides' => ['meg_source_localization_ecd', 'meg_source_localization_loreta'],
    ],
    'discharge_summary' => [
        'name' => 'Discharge Summary',
        'slides' => ['summary_localization_discharge_4view', 'summary_localization_discharge_axial', 'summary_localization_discharge_coronal'],
    ],
    'functional_mapping' => [
        'name' => 'Functional Mapping',
        'slides' => [
            'functional_mapping_sensory_median',
            'functional_mapping_sensory_pneumatic',
            'functional_mapping_motor',
            'functional_mapping_motor_finger',
            'functional_mapping_auditory',
            'functional_mapping_language',
            'functional_mapping_visual',
        ],
    ],
    'localization_summary' => [
        'name' => 'Localization Summary',
        'slides' => ['meg_localization_summary_3', 'meg_localization_summary_4', 'meg_localization_summary_5'],
    ],
    'summary' => [
        'name' => 'Summary',
        'slides' => ['summary'],
    ],
]));