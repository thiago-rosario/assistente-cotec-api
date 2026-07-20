<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\TravelReport\Application\Interface\Adapter\FindTravelReportBySeiProcessAdapterInterface;
use App\Core\TravelReport\Application\Interface\Usecase\FindTravelReportBySeiProcessUsecaseInterface;
use App\Core\TravelReport\Exception\SeiProcessRequiredException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\FindTravelReportBySeiProcessRequest;
use Illuminate\Http\JsonResponse;

class FindTravelReportBySeiProcessController extends Controller
{
    public function __construct(
        private readonly FindTravelReportBySeiProcessUsecaseInterface $usecase,
        private readonly FindTravelReportBySeiProcessAdapterInterface $adapter,
    ) {}

    public function __invoke(FindTravelReportBySeiProcessRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->toInput($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray());
        } catch (SeiProcessRequiredException $e) {
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
