<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Enum;

enum SeiProcessDecision: string
{
    case Pending = 'pending';

    case Provided = 'provided';

    case NotProvided = 'not_provided';
}
