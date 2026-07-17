<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Enum;

enum TravelReportCodeExceptionEnum: int
{
    case SubmittedByUserIdRequired = 1011;
    case FileNameRequired = 1012;
    case FilePathRequired = 1013;
    case InvalidMunicipalityId = 1014;
    case SeiProcessRequired = 1015;
}
