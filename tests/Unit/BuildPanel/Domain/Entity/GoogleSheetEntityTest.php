<?php

use App\Core\BuildPanel\Domain\Entity\GoogleSheetEntity;
use App\Core\BuildPanel\Exception\GoogleSheetGidInvalidException;
use App\Core\BuildPanel\Exception\GoogleSheetNameRequiredException;
use App\Core\BuildPanel\Exception\GoogleSpreadsheetIdRequiredException;

it('creates google sheet entities from configured sheets', function () {
    $sheets = GoogleSheetEntity::fromConfiguredSheets('spreadsheet-id', [
        10 => 'Caderno',
        20 => "Equipe's Sheet",
    ]);

    expect($sheets)->toHaveCount(2)
        ->and($sheets[0]->spreadsheetId)->toBe('spreadsheet-id')
        ->and($sheets[0]->gid)->toBe(10)
        ->and($sheets[0]->name)->toBe('Caderno')
        ->and($sheets[1]->quotedRangeName())->toBe("'Equipe''s Sheet'")
        ->and($sheets[1]->toDiagnosticArray())->toBe([
            'gid' => 20,
            'name' => "Equipe's Sheet",
        ]);
});

it('requires a spreadsheet id', function () {
    new GoogleSheetEntity('', 10, 'Caderno');
})->throws(GoogleSpreadsheetIdRequiredException::class, 'O identificador da planilha Google deve ser informado.');

it('requires a valid sheet gid', function () {
    new GoogleSheetEntity('spreadsheet-id', 0, 'Caderno');
})->throws(GoogleSheetGidInvalidException::class, 'O gid da aba da planilha Google deve ser maior que zero.');

it('requires a sheet name', function () {
    new GoogleSheetEntity('spreadsheet-id', 10, '');
})->throws(GoogleSheetNameRequiredException::class, 'O nome da aba da planilha Google deve ser informado.');

it('requires at least one configured sheet', function () {
    GoogleSheetEntity::fromConfiguredSheets('spreadsheet-id', []);
})->throws(InvalidArgumentException::class, 'A planilha Google deve possuir ao menos uma aba configurada.');

it('requires unique sheet names', function () {
    GoogleSheetEntity::fromConfiguredSheets('spreadsheet-id', [
        10 => 'Caderno',
        20 => ' caderno ',
    ]);
})->throws(InvalidArgumentException::class, 'As abas da planilha Google devem possuir nomes únicos.');
