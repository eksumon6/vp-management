<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

return [

    // -------- Document options --------
    'mode'                  => 'utf-8',
    'format'                => 'A4',
    'orientation'           => 'P',
    'default_font_size'     => 12,

    // ✅ family নাম: adorsho (CSS ও Controller এই family ব্যবহার করবে)
    'default_font'          => 'adorsho',

    'margin_left'           => 10,
    'margin_right'          => 10,
    'margin_top'            => 10,
    'margin_bottom'         => 10,
    'display_mode'          => 'fullpage',

    // -------- Temp & font dirs --------
    'tempDir'               => storage_path('app/mpdf-temp'),
    'fontDir'               => [ public_path('fonts') ],

    'fontdata'              => (function () {
        $fontFile = env('PDF_PRIMARY_FONT_FILE', 'Nikosh.ttf'); // ← শুধু এটুকুই বদলান
        return [
            'adorsho' => [
                'R' => $fontFile, // Regular
                'B' => $fontFile, // Bold আলাদা না থাকলে একই ফাইল
            ],
        ];
    })(),

    // -------- Shaping / language --------
    'useOTL'                => 0xFF,   // Bengali shaping ON
    'useKashida'            => 0,

    // একটাই ফন্ট, তাই auto fallback নিষ্ক্রিয় রাখা নিরাপদ
    'autoScriptToLang'      => false,
    'autoLangToFont'        => false,
];
