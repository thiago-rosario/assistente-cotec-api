<?php

use App\Core\Application\Interfaces\ConstructionDemandSheetMapperInterface;
use App\Core\Application\Interfaces\LandSurveySheetMapperInterface;
use App\Core\Application\Interfaces\TechnicalNotebookSheetMapperInterface;
use App\Core\Application\Interfaces\TravelItinerarySheetMapperInterface;
use App\Core\Infra\Mapper\ConstructionDemandSheetMapper;
use App\Core\Infra\Mapper\LandSurveySheetMapper;
use App\Core\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Core\Infra\Mapper\TravelItinerarySheetMapper;

it('resolves spreadsheet mapper interfaces from the container', function (
    string $abstract,
    string $concrete,
) {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [ConstructionDemandSheetMapperInterface::class, ConstructionDemandSheetMapper::class],
    [LandSurveySheetMapperInterface::class, LandSurveySheetMapper::class],
    [TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class],
    [TravelItinerarySheetMapperInterface::class, TravelItinerarySheetMapper::class],
]);
