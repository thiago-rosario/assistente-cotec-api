<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\TravelReport\Application\Interface\Adapter\DeleteTravelReportAdapterInterface;
use App\Core\TravelReport\Application\Interface\Usecase\DeleteTravelReportUsecaseInterface;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\DeleteTravelReportRequest;
use Illuminate\Http\JsonResponse;

class DeleteTravelReportController extends Controller
{
    public function __construct(
        private readonly DeleteTravelReportUsecaseInterface $usecase,
        private readonly DeleteTravelReportAdapterInterface $adapter,
    ) {}

    public function __invoke(DeleteTravelReportRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->toInput($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray());
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
