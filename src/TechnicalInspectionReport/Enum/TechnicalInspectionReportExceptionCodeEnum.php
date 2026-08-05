<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Enum;

enum TechnicalInspectionReportExceptionCodeEnum: int
{
    case Domain = 2000;

    case InvalidValue = 2001;

    case InvalidReportId = 2002;

    case InvalidExternalMessageId = 2003;

    case InvalidMunicipality = 2004;

    case InvalidSeiProcess = 2005;

    case InvalidInspectionDate = 2006;

    case InvalidResponsiblePerson = 2007;

    case InvalidFile = 2008;

    case IncompleteReport = 2009;

    case InvalidStateTransition = 2010;

    case InvalidReportForCataloging = 2011;
}
