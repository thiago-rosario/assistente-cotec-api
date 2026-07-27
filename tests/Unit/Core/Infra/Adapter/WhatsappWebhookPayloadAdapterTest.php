<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Domain\Resolver\PhoneNormalizerResolver;
use App\Core\Exception\MessageNotContentException;
use App\Core\Infra\Adapter\PythonMessagePayloadAdapter;
use App\Core\Infra\Adapter\WhatsappWebhookPayloadAdapter;
use App\Core\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Infra\Mapper\WhatsappWebhookPayloadMapper;
use App\Core\Infra\Parser\PythonBridgeEventParser;
use App\Core\Infra\Parser\PythonMessageOutputParser;
use Tests\TestCase;

uses(TestCase::class);

function whatsappWebhookPayloadAdapter(): WhatsappWebhookPayloadAdapter
{
    return new WhatsappWebhookPayloadAdapter(
        mapper: new WhatsappWebhookPayloadMapper,
        resolver: new PhoneNormalizerResolver,
    );
}

function pythonMessagePayloadAdapter(): PythonMessagePayloadAdapter
{
    return new PythonMessagePayloadAdapter(
        mapper: new PythonMessagePayloadMapper,
        parser: new PythonMessageOutputParser(
            bridgeEventParser: new PythonBridgeEventParser,
        ),
        resolver: new PhoneNormalizerResolver,
    );
}

it('maps EditaCodigo webhook payload into the application dto', function () {
    $dto = whatsappWebhookPayloadAdapter()->fromArray([
        'customer_contact' => '5571999999999',
        'content' => 'Preciso de atendimento',
        'sender_name' => 'Maria',
        'received_at' => '2026-07-27T14:30:00-03:00',
        'external_id' => 'editacodigo-123',
        'source' => 'editacodigo',
    ]);

    expect($dto)->toBeInstanceOf(ReceivedMessageInputDTO::class)
        ->and($dto->message)->toBe('Preciso de atendimento')
        ->and($dto->phone)->toBe('5571999999999')
        ->and($dto->senderName)->toBe('Maria')
        ->and($dto->receivedAt)->toBe('2026-07-27T14:30:00-03:00')
        ->and($dto->source)->toBe('editacodigo')
        ->and($dto->externalId)->toBe('editacodigo-123');
});

it('maps accepted aliases into the application dto', function () {
    $dto = whatsappWebhookPayloadAdapter()->fromArray([
        'last_message' => [
            'content' => 'Buscar Salvador',
            'customer_contact' => 'whatsapp:+55 (71) 98888-7777',
        ],
        'name' => 'Thiago',
        'timestamp' => '2026-07-27T15:00:00-03:00',
        'message_id' => 'alias-123',
    ]);

    expect($dto->message)->toBe('Buscar Salvador')
        ->and($dto->phone)->toBe('+5571988887777')
        ->and($dto->senderName)->toBe('Thiago')
        ->and($dto->receivedAt)->toBe('2026-07-27T15:00:00-03:00')
        ->and($dto->source)->toBe('whatsapp-webhook')
        ->and($dto->externalId)->toBe('alias-123');
});

it('resolves the webhook adapter interface from the container', function () {
    $adapter = app(WhatsappWebhookPayloadAdapterInterface::class);

    expect($adapter)->toBeInstanceOf(WhatsappWebhookPayloadAdapter::class);
});

it('keeps the legacy python adapter interface available for fallback', function () {
    $adapter = app(PythonMessagePayloadAdapterInterface::class);

    expect($adapter)->toBeInstanceOf(PythonMessagePayloadAdapter::class);
});

it('rejects payload without message content', function () {
    expect(fn () => whatsappWebhookPayloadAdapter()->fromArray(['customer_contact' => '+5511912345678']))
        ->toThrow(MessageNotContentException::class, 'Payload de mensagem recebido sem conteúdo.');
});

it('keeps mapping python bridge json events for the legacy fallback', function () {
    $messages = pythonMessagePayloadAdapter()->fromPythonOutput(<<<'OUTPUT'
        Aguardando login no WhatsApp Web...
        {"type": "received_message", "payload": {"customer_contact": "Gpairiito", "content": "Ou nem ?", "source": "python-whatsapp", "external_id": "msg-456", "timestamp": "15/06/2026, 15:01:37"}}
        {"type": "received_message", "payload": {"customer_contact": "+5511999999999", "content": "Oi", "source": "python-whatsapp"}}
        OUTPUT);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]->senderName)->toBe('Gpairiito')
        ->and($messages[0]->phone)->toBeNull()
        ->and($messages[0]->message)->toBe('Ou nem ?')
        ->and($messages[0]->source)->toBe('python-whatsapp')
        ->and($messages[0]->externalId)->toBe('msg-456')
        ->and($messages[0]->receivedAt)->toBe('15/06/2026, 15:01:37')
        ->and($messages[1]->senderName)->toBeNull()
        ->and($messages[1]->phone)->toBe('+5511999999999')
        ->and($messages[1]->message)->toBe('Oi');
});
