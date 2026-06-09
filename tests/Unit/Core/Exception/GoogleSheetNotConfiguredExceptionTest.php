<?php

use App\Core\Enum\CodeExceptionEnum;
use App\Core\Exception\GoogleSheetNotConfiguredException;

it('defines google sheet not configured exception defaults', function () {
    $exception = new GoogleSheetNotConfiguredException(sheetId: 999);

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->sheetId)->toBe(999)
        ->and($exception->getMessage())->toBe('A aba informada não está configurada para consulta.')
        ->and($exception->getCode())->toBe(CodeExceptionEnum::GoogleSheetNotConfigured->value);
});
