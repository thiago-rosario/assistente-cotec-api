<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Domain\Resolver\PhoneNormalizerResolver;
use App\Core\Exception\MessageNotContentException;
use App\Core\Infra\Adapter\WhatsappWebhookPayloadAdapter;
use App\Core\Infra\Mapper\WhatsappWebhookPayloadMapper;
use Tests\TestCase;

uses(TestCase::class);

function whatsappWebhookPayloadAdapter(): WhatsappWebhookPayloadAdapter
{
    return new WhatsappWebhookPayloadAdapter(
        mapper: new WhatsappWebhookPayloadMapper,
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

it('maps real EditaCodigo aliases into the application dto', function () {
    $dto = whatsappWebhookPayloadAdapter()->fromArray([
        'telefone' => '5571999999999',
        'texto' => 'Consultar Andaraí',
        'id_mensagem' => 'editacodigo-real-001',
        'timestamp' => 1785162600,
    ]);

    expect($dto)->toBeInstanceOf(ReceivedMessageInputDTO::class)
        ->and($dto->message)->toBe('Consultar Andaraí')
        ->and($dto->phone)->toBe('5571999999999')
        ->and($dto->receivedAt)->toBe('1785162600')
        ->and($dto->source)->toBe('whatsapp-webhook')
        ->and($dto->externalId)->toBe('editacodigo-real-001');
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

it('rejects payload without message content', function () {
    expect(fn () => whatsappWebhookPayloadAdapter()->fromArray(['customer_contact' => '+5511912345678']))
        ->toThrow(MessageNotContentException::class, 'Payload de mensagem recebido sem conteúdo.');
});
