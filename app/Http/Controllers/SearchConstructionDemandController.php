<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Application\Interfaces\Adapter\SearchConstructionDemandAdapterInterface;
use App\Core\Application\Interfaces\Usecase\SearchConstructionDemandUsecaseInterface;
use App\Core\Exception\SearchConstructionDemandException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\SearchConstructionDemandRequest;
use Illuminate\Http\JsonResponse;

class SearchConstructionDemandController extends Controller
{
    public function __construct(
        private readonly SearchConstructionDemandAdapterInterface $adapter,
        private readonly SearchConstructionDemandUsecaseInterface $usecase,
    ) {}

    public function __invoke(SearchConstructionDemandRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray());
        } catch (SearchConstructionDemandException $e) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return response()
                ->json($response->toArray(), $e->getCode());
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
