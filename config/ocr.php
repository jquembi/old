<?php

return [
    'language' => env('OCR_LANGUAGE', 'pt-PT'),
    'max_pages' => (int) env('OCR_MAX_PAGES', 20),
    'max_tiles' => (int) env('OCR_MAX_TILES', 16),
    'timeout' => (int) env('OCR_TIMEOUT', 120),
];
