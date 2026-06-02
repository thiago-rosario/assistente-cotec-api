<?php

use App\Core\Application\Interfaces\ConstructionDemandSheetMapperInterface;
use App\Core\Application\Interfaces\LandSurveySheetMapperInterface;
use App\Core\Application\Interfaces\NotebookSheetMapperInterface;
use App\Core\Application\Interfaces\TechnicalNotebookSheetMapperInterface;
use App\Core\Application\Interfaces\TravelItinerarySheetMapperInterface;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;
use App\Core\Domain\Repository\NotebookRepositoryInterface;
use App\Core\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\Core\Infra\Mapper\ConstructionDemandSheetMapper;
use App\Core\Infra\Mapper\LandSurveySheetMapper;
use App\Core\Infra\Mapper\NotebookSheetMapper;
use App\Core\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Core\Infra\Mapper\TravelItinerarySheetMapper;
use App\Core\Infra\Repository\Gateway\ConstructionDemandGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\LandSurveyGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\NotebookGoogleSheetGatewayRepository;
use App\Core\Infra\Repository\Gateway\TechnicalNotebookGoogleSheetGatewayRepository;

it('resolves spreadsheet mapper interfaces from the container', function (
    string $abstract,
    string $concrete,
) {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [ConstructionDemandSheetMapperInterface::class, ConstructionDemandSheetMapper::class],
    [LandSurveySheetMapperInterface::class, LandSurveySheetMapper::class],
    [NotebookSheetMapperInterface::class, NotebookSheetMapper::class],
    [TechnicalNotebookSheetMapperInterface::class, TechnicalNotebookSheetMapper::class],
    [TravelItinerarySheetMapperInterface::class, TravelItinerarySheetMapper::class],
    [ConstructionDemandRepositoryInterface::class, ConstructionDemandGoogleSheetGatewayRepository::class],
    [LandSurveyRepositoryInterface::class, LandSurveyGoogleSheetGatewayRepository::class],
    [NotebookRepositoryInterface::class, NotebookGoogleSheetGatewayRepository::class],
    [TechnicalNotebookRepositoryInterface::class, TechnicalNotebookGoogleSheetGatewayRepository::class],
]);
