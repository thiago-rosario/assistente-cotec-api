<?php

use App\Core\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Application\Interfaces\Adapter\WhatsappWebhookPayloadAdapterInterface;
use App\Core\Application\Usecase\AcceptIncomingWhatsappWebhookUsecase;
use App\Jobs\ProcessIncomingWhatsappMessageJob;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config(['cache.default' => 'array']);

    Cache::flush();
    Bus::fake();
    Log::spy();
});

it('reserves an external id and dispatches the processing job', function () {
    $input = new ReceivedMessageInputDTO(
        message: 'Olá',
        phone: '5571999999999',
        source: 'editacodigo',
        externalId: 'accepted-001',
    );

    $result = app(AcceptIncomingWhatsappWebhookUsecase::class)($input);

    expect($result)->toBe([
        'accepted' => true,
        'external_id' => 'accepted-001',
        'duplicate' => false,
    ]);

    Bus::assertDispatched(ProcessIncomingWhatsappMessageJob::class, function (ProcessIncomingWhatsappMessageJob $job): bool {
        return $job->payload()['external_id'] === 'accepted-001';
    });
});

it('does not dispatch a duplicated external id twice', function () {
    $input = new ReceivedMessageInputDTO(
        message: 'Olá',
        phone: '5571999999999',
        source: 'editacodigo',
        externalId: 'duplicated-002',
    );
    $usecase = app(AcceptIncomingWhatsappWebhookUsecase::class);

    $first = $usecase($input);
    $second = $usecase($input);

    expect($first['accepted'])->toBeTrue()
        ->and($first['duplicate'])->toBeFalse()
        ->and($second)->toBe([
            'accepted' => false,
            'external_id' => 'duplicated-002',
            'duplicate' => true,
        ]);

    Bus::assertDispatchedTimes(ProcessIncomingWhatsappMessageJob::class, 1);
});

it('does not block messages without external id', function () {
    $input = new ReceivedMessageInputDTO(
        message: 'Olá',
        phone: '5571999999999',
        source: 'editacodigo',
    );
    $usecase = app(AcceptIncomingWhatsappWebhookUsecase::class);

    $usecase($input);
    $usecase($input);

    Bus::assertDispatchedTimes(ProcessIncomingWhatsappMessageJob::class, 2);
});

it('releases the idempotency reservation when dispatch fails', function () {
    Bus::swap(app(Dispatcher::class));

    $dispatcher = Mockery::mock(Dispatcher::class);
    $dispatcher->shouldReceive('dispatch')
        ->once()
        ->andThrow(new RuntimeException('Queue unavailable'));

    $input = new ReceivedMessageInputDTO(
        message: 'Olá',
        phone: '5571999999999',
        source: 'editacodigo',
        externalId: 'dispatch-failed-001',
    );

    $usecase = new AcceptIncomingWhatsappWebhookUsecase(
        cache: Cache::store(),
        dispatcher: $dispatcher,
        adapter: app(WhatsappWebhookPayloadAdapterInterface::class),
    );

    expect(fn () => $usecase($input))->toThrow(RuntimeException::class, 'Queue unavailable')
        ->and(Cache::has('whatsapp:incoming:dispatch-failed-001'))->toBeFalse();
});
