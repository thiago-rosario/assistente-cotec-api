<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\BuildPanel\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\BuildPanel\Exception\SearchTechnicalNotebookException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\SearchTechnicalNotebookRequest;
use Illuminate\Http\JsonResponse;

class SearchTechnicalNotebookController extends Controller
{
    public function __construct(
        private readonly SearchTechnicalNotebookAdapterInterface $adapter,
        private readonly SearchTechnicalNotebookUsecaseInterface $usecase,
    ) {}

    public function __invoke(SearchTechnicalNotebookRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray());
        } catch (SearchTechnicalNotebookException $e) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return response()
                ->json($response->toArray(), 500);
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
