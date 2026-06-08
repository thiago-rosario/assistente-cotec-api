<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Application\Interfaces\Adapter\SearchGoogleSheetAdapterInterface;
use App\Core\Application\Interfaces\Usecase\SearchGoogleSheetUsecaseInterface;
use App\Core\Exception\GoogleSheetNotConfiguredException;
use App\Core\Exception\GoogleSheetReadException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\SearchGoogleSheetRequest;
use Illuminate\Http\JsonResponse;

class SearchGoogleSheetController extends Controller
{
    public function __construct(
        private readonly SearchGoogleSheetUsecaseInterface $usecase,
        private readonly SearchGoogleSheetAdapterInterface $adapter,
    ) {}

    public function __invoke(SearchGoogleSheetRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray());
        } catch (GoogleSheetNotConfiguredException $e) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return response()
                ->json($response->toArray(), 400);
        } catch (GoogleSheetReadException $e) {
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
