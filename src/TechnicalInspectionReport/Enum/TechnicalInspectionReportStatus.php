<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Enum;

enum TechnicalInspectionReportStatus: string
{
    case Draft = 'draft';

    case ReadyForStorage = 'ready_for_storage';

    case StoragePending = 'storage_pending';

    case Stored = 'stored';

    case StorageFailed = 'storage_failed';
}
