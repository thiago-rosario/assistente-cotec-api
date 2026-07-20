<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\TravelReport\Application\Interface\Adapter\PersistTravelReportAdapterInterface;
use App\Core\TravelReport\Application\Interface\Usecase\PersistTravelReportUsecaseInterface;
use App\Core\TravelReport\Exception\FileNameRequiredException;
use App\Core\TravelReport\Exception\FilePathRequiredException;
use App\Core\TravelReport\Exception\InvalidMunicipalityIdException;
use App\Core\TravelReport\Exception\SeiProcessRequiredException;
use App\Core\TravelReport\Exception\SubmittedByUserIdRequiredException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\PersistTravelReportRequest;
use Illuminate\Http\JsonResponse;

class PersistTravelReportController extends Controller
{
    public function __construct(
        private readonly PersistTravelReportUsecaseInterface $usecase,
        private readonly PersistTravelReportAdapterInterface $adapter,
    ) {}

    public function __invoke(PersistTravelReportRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->toInput($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray(), 201);
        } catch (
            FileNameRequiredException|
            FilePathRequiredException|
            InvalidMunicipalityIdException|
            SeiProcessRequiredException|
            SubmittedByUserIdRequiredException $e
        ) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return response()
                ->json($response->toArray(), 400);
        } catch (\Throwable) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: 'An unexpected error occurred',
                code: 500,
            );

            return response()
                ->json($response->toArray(), 500);
        }
    }
}
