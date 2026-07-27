<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Interfaces\Usecase\AcceptIncomingWhatsappWebhookUsecaseInterface;
use App\Core\Exception\MessageNotContentException;
use App\Core\Exception\WhatsapppMessageException;
use App\Http\Helper\ResponseJsend;
use App\Http\Requests\WhatsappMessageRequest;
use Illuminate\Http\JsonResponse;

class WhatsappMessageController extends Controller
{
    public function __construct(
        private readonly AcceptIncomingWhatsappWebhookUsecaseInterface $usecase,
        private readonly WhatsappWebhookPayloadAdapterInterface $adapter,
    ) {}

    public function __invoke(WhatsappMessageRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($result);

            return response()
                ->json($response->toArray(), 202);
        } catch (MessageNotContentException $e) {
            $response = new ResponseJsend(
                data: [
                    'message' => [$e->getMessage()],
                ],
                status: ResponseJsend::STATUS_FAIL,
            );

            return response()
                ->json($response->toArray(), 422);
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
