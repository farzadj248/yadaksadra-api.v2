<?php

return [
    'mode'                     => '',
    'format'                   => 'A5',
    'default_font_size'        => '8',
    'default_font'             => 'sans-serif',
    'margin_left'              => 5,
    'margin_right'             => 5,
    'margin_top'               => 4,
    'margin_bottom'            => 5,
    'margin_header'            => 0,
    'margin_footer'            => 0,
    'orientation'              => 'P',
    'title'                    => 'یدک صدرا',
    'subject'                  => '',
    'author'                   => '',
    'watermark'                => 'Yadak Sadra',
    'show_watermark'           => true,
    'show_watermark_image'     => false,
    'watermark_font'           => 'sans-serif',
    'display_mode'             => 'fullpage',
    'watermark_text_alpha'     => 0.1,
    'watermark_image_path'     => '',
    'watermark_image_alpha'    => 0.2,
    'watermark_image_size'     => 'D',
    'watermark_image_position' => 'P',
    'custom_font_dir'          => '',
    'custom_font_data'         => [],
    'auto_language_detection'  => true,
    'temp_dir'                 => storage_path('app'),
    'pdfa'                     => true,
    'pdfaauto'                 => true,
    'use_active_forms'         => true,
	'tempDir' => base_path('storage/temp'),
	'font_path' => base_path('storage/fonts/'),
	'font_data' => [
		'IRANSansWeb' => [
			'R' => 'IRANSansWeb.ttf', // regular font
			'B' => 'IRANSansWeb.ttf', // optional: bold font
			'I' => 'IRANSansWeb.ttf', // optional: italic font
			'BI' => 'IRANSansWeb.ttf', // optional: bold-italic font
			'useOTL' => 0xFF, // required for complicated langs like Persian, Arabic and Chinese
			'useKashida' => 75, // required for complicated langs like Persian, Arabic and Chinese
		]
	]
];
