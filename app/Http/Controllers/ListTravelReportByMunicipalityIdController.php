<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\TravelReport\Application\Interface\Adapter\ListTravelReportByMunicipalityIdAdapterInterface;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportByMunicipalityIdUsecaseInterface;
use App\Core\TravelReport\Exception\InvalidMunicipalityIdException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\ListTravelReportByMunicipalityIdRequest;
use Illuminate\Http\JsonResponse;

class ListTravelReportByMunicipalityIdController extends Controller
{
    public function __construct(
        private readonly ListTravelReportByMunicipalityIdUsecaseInterface $usecase,
        private readonly ListTravelReportByMunicipalityIdAdapterInterface $adapter,
    ) {}

    public function __invoke(ListTravelReportByMunicipalityIdRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->toInput($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray());
        } catch (InvalidMunicipalityIdException $e) {
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
