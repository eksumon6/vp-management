<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

return [

    'mode'                  => 'utf-8',
    'format'                => 'A4',
    'default_font_size'     => '12',
    // ডিফল্ট Nikosh, তবে CSS-এ নির্দিষ্ট জায়গায় Noto-কে অগ্রাধিকার দেব
    'default_font'          => 'nikosh',
    'margin_left'           => 10,
    'margin_right'          => 10,
    'margin_top'            => 10,
    'margin_bottom'         => 10,
    'orientation'           => 'P',
    'display_mode'          => 'fullpage',

    'temp_dir'              => storage_path('app/mpdf-temp'),

    'font_path' => public_path('fonts/'),
    'font_data' => [
        // Nikosh Unicode
        'nikosh' => [
            'R' => 'Nikosh.ttf',
            'B' => 'Nikosh.ttf',
            'useOTL'     => 0xFF, // Indic shaping ON
            'useKashida' => 0,
        ],
        // Fallback: Noto Sans Bengali (রেন্ডারিং সবচেয়ে স্থিতিশীল)
        'notobn' => [
            'R' => 'NotoSansBengali-Regular.ttf',
            'B' => 'NotoSansBengali-Bold.ttf',
            'useOTL'     => 0xFF,
            'useKashida' => 0,
        ],
    ],

    'auto_script_to_lang'   => true,
    'auto_lang_to_font'     => true,

    'pdf_a'                 => false,
    'pdf_a_auto'            => false,
    'use_active_forms'      => false,
];
