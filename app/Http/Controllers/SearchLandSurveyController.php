<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Application\Interfaces\Adapter\SearchLandSurveyAdapterInterface;
use App\Core\Application\Interfaces\Usecase\SearchLandSurveyUsecaseInterface;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\SearchLandSurveyRequest;
use Illuminate\Http\JsonResponse;

class SearchLandSurveyController extends Controller
{
    public function __construct(
        private readonly SearchLandSurveyAdapterInterface $adapter,
        private readonly SearchLandSurveyUsecaseInterface $usecase,
    ) {}

    public function __invoke(SearchLandSurveyRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

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
