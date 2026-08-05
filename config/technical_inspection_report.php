<?php

declare(strict_types=1);

return [
    'google_drive' => [
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
    ],

    'google_sheet' => [
        'spreadsheet_id' => env('GOOGLE_TECHNICAL_INSPECTION_REPORT_SPREADSHEET_ID'),
        'sheet_name' => env('GOOGLE_TECHNICAL_INSPECTION_REPORT_SHEET_NAME'),
    ],
];
