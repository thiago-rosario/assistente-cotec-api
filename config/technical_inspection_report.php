<?php

declare(strict_types=1);

return [
    'conversation_state_ttl' => (int) env('TECHNICAL_INSPECTION_REPORT_CONVERSATION_TTL', 86400),
    'max_document_size_bytes' => (int) env('TECHNICAL_INSPECTION_REPORT_MAX_DOCUMENT_SIZE_BYTES', 10 * 1024 * 1024),

    'google_drive' => [
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
    ],

    'google_sheet' => [
        'spreadsheet_id' => env('GOOGLE_TECHNICAL_INSPECTION_REPORT_SPREADSHEET_ID'),
        'sheet_name' => env('GOOGLE_TECHNICAL_INSPECTION_REPORT_SHEET_NAME'),
    ],
];
