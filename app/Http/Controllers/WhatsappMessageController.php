<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Exception\WhatsapppMessageException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\WhatsappMessageRequest;
use Illuminate\Http\JsonResponse;

class WhatsappMessageController extends Controller
{
    public function __construct(
        private readonly ProcessWhatsappMessageUsecaseInterface $usecase,
        private readonly PythonMessagePayloadAdapterInterface $adapter,
    ) {}

    public function __invoke(WhatsappMessageRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($result);

            return response()
                ->json($response->toArray());
        } catch (WhatsapppMessageException $e) {
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
