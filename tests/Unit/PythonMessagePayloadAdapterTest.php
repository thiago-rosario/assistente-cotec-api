<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\PythonMessagePayloadAdapterInterface;
use App\Core\Domain\Resolver\PhoneNormalizerResolver;
use App\Core\Exception\MessageNotContentException;
use App\Core\Infra\Adapter\PythonMessagePayloadAdapter;
use App\Core\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Infra\Parser\PythonBridgeEventParser;
use App\Core\Infra\Parser\PythonMessageOutputParser;
use Tests\TestCase;

uses(TestCase::class);

function pythonMessagePayloadAdapter(): PythonMessagePayloadAdapter
{
    $mapper = new PythonMessagePayloadMapper;

    return new PythonMessagePayloadAdapter(
        mapper: $mapper,
        parser: new PythonMessageOutputParser(
            bridgeEventParser: new PythonBridgeEventParser,
        ),
        resolver: new PhoneNormalizerResolver,
    );
}

it('maps the real python bot output into application dtos', function () {
    $adapter = pythonMessagePayloadAdapter();

    $messages = $adapter->fromPythonOutput(<<<'OUTPUT'
        Aguardando login no WhatsApp Web...
        Nenhuma mensagem nova encontrada. Aguardando login ou carregamento do WhatsApp Web.
        Mensagem recebida de: Gpairiito
        Conteúdo da mensagem: Ou nem ?
        20:12
        Mensagem recebida de: Tonha Oi
        Conteúdo da mensagem: É isso aí descansa
        13:11
        Mensagem recebida de: Swat
        Conteúdo da mensagem: Oiii
        17:13
        ^CTraceback (most recent call last):
          File "/Users/thiago/INOVA/assistente-cotec-api/src/Core/Infra/External/Python/main.py", line 29, in <module>
            main()
        KeyboardInterrupt
        OUTPUT);

    expect($messages)->toHaveCount(3)
        ->and($messages[0])->toBeInstanceOf(ReceivedMessageInputDTO::class)
        ->and($messages[0]->senderName)->toBe('Gpairiito')
        ->and($messages[0]->message)->toBe('Ou nem ?')
        ->and($messages[0]->receivedAt)->toBe('20:12')
        ->and($messages[1]->senderName)->toBe('Tonha Oi')
        ->and($messages[1]->message)->toBe('É isso aí descansa')
        ->and($messages[1]->receivedAt)->toBe('13:11')
        ->and($messages[2]->senderName)->toBe('Swat')
        ->and($messages[2]->message)->toBe('Oiii')
        ->and($messages[2]->receivedAt)->toBe('17:13');
});

it('maps python bridge json events into application dtos', function () {
    $adapter = pythonMessagePayloadAdapter();

    $messages = $adapter->fromPythonOutput(<<<'OUTPUT'
        Aguardando login no WhatsApp Web...
        {"type": "received_message", "payload": {"customer_contact": "Gpairiito", "content": "Ou nem ?", "source": "python-whatsapp"}}
        {"type": "received_message", "payload": {"customer_contact": "+5511999999999", "content": "Oi", "source": "python-whatsapp"}}
        OUTPUT);

    expect($messages)->toHaveCount(2)
        ->and($messages[0]->senderName)->toBe('Gpairiito')
        ->and($messages[0]->phone)->toBeNull()
        ->and($messages[0]->message)->toBe('Ou nem ?')
        ->and($messages[1]->senderName)->toBeNull()
        ->and($messages[1]->phone)->toBe('+5511999999999')
        ->and($messages[1]->message)->toBe('Oi');
});

it('maps python bridge events without text content into application dtos', function () {
    $adapter = pythonMessagePayloadAdapter();

    $messages = $adapter->fromPythonOutput(<<<'OUTPUT'
        {"type": "received_message", "payload": {"customer_contact": "Thiago", "content": "", "content_detected": false, "source": "python-whatsapp"}}
        OUTPUT);

    expect($messages)->toHaveCount(1)
        ->and($messages[0]->senderName)->toBe('Thiago')
        ->and($messages[0]->message)->toBe('')
        ->and($messages[0]->metadata['content_detected'])->toBeFalse();
});

it('maps structured python whatsapp payload into the application dto', function () {
    $adapter = pythonMessagePayloadAdapter();

    $dto = $adapter->fromArray([
        'customer_contact' => 'whatsapp:+5511912345678',
        'content' => 'Preciso de atendimento',
        'customer_name' => 'Maria',
        'timestamp' => '2026-05-27T20:00:00-03:00',
        'message_id' => 'msg-123',
    ]);

    expect($dto)->toBeInstanceOf(ReceivedMessageInputDTO::class)
        ->and($dto->message)->toBe('Preciso de atendimento')
        ->and($dto->phone)->toBe('+5511912345678')
        ->and($dto->senderName)->toBe('Maria')
        ->and($dto->receivedAt)->toBe('2026-05-27T20:00:00-03:00')
        ->and($dto->source)->toBe('python-whatsapp')
        ->and($dto->externalId)->toBe('msg-123');
});

it('resolves the adapter interface from the container', function () {
    $adapter = app(PythonMessagePayloadAdapterInterface::class);

    expect($adapter)->toBeInstanceOf(PythonMessagePayloadAdapter::class);
});

it('rejects payload without message content', function () {
    $adapter = pythonMessagePayloadAdapter();

    expect(fn () => $adapter->fromArray(['customer_contact' => '+5511912345678']))
        ->toThrow(MessageNotContentException::class, 'Payload de mensagem recebido sem conteúdo.');
});
