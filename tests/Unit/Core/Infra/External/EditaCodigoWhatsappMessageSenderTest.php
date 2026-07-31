<?php

use App\Core\Exception\EditaCodigoWhatsappMessageSenderException;
use App\Core\Infra\External\EditaCodigoWhatsappMessageSender;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'services.editacodigo_bot.webhook_url' => 'https://host.docker.internal:8443/',
        'services.editacodigo_bot.user' => 'editacodigo_user',
        'services.editacodigo_bot.token' => 'secret-token',
        'services.editacodigo_bot.timeout' => 15,
        'services.editacodigo_bot.retry_times' => 3,
        'services.editacodigo_bot.verify_tls' => true,
    ]);

    Http::preventStrayRequests();
    Log::spy();
});

it('sends the EditaCodigo whatsapp message contract with json headers', function () {
    Http::fake([
        'https://host.docker.internal:8443/' => Http::response(['ok' => true], 200),
    ]);

    (new EditaCodigoWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-001',
    );

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://host.docker.internal:8443/'
            && $request->method() === 'POST'
            && $request->hasHeader('Accept', 'application/json')
            && str_contains((string) ($request->header('Content-Type')[0] ?? ''), 'application/json')
            && $request['usuario'] === 'editacodigo_user'
            && $request['token'] === 'secret-token'
            && $request['action'] === 'EnviarMsg'
            && $request['message']['telefone'] === '5571999999999'
            && $request['message']['msg'] === 'Resposta gerada pelo assistente'
            && $request['message']['id_msg'] === 'message-001';
    });
});

it('disables TLS verification when configured for the local EditaCodigo certificate', function () {
    config(['services.editacodigo_bot.verify_tls' => false]);

    $request = Mockery::mock(PendingRequest::class);
    $response = Mockery::mock(Response::class);

    Http::shouldReceive('timeout')
        ->once()
        ->with(15)
        ->andReturn($request);

    $request->shouldReceive('connectTimeout')
        ->once()
        ->with(3)
        ->andReturnSelf();
    $request->shouldReceive('retry')
        ->once()
        ->with(3, Mockery::type(Closure::class), Mockery::type(Closure::class))
        ->andReturnSelf();
    $request->shouldReceive('acceptJson')->once()->andReturnSelf();
    $request->shouldReceive('asJson')->once()->andReturnSelf();
    $request->shouldReceive('withoutVerifying')->once()->andReturnSelf();
    $request->shouldReceive('throw')->once()->andReturnSelf();
    $request->shouldReceive('post')
        ->once()
        ->with('https://host.docker.internal:8443/', Mockery::on(
            fn (array $payload): bool => $payload['message']['telefone'] === '5571999999999'
                && $payload['message']['msg'] === 'Resposta gerada pelo assistente'
        ))
        ->andReturn($response);

    $response->shouldReceive('status')->once()->andReturn(200);

    (new EditaCodigoWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-005',
    );
});

it('retries temporary EditaCodigo http errors', function () {
    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        $attempts++;

        return $attempts === 1
            ? Http::response(['error' => 'temporary'], 500)
            : Http::response(['ok' => true], 200);
    });

    (new EditaCodigoWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-002',
    );

    expect($attempts)->toBe(2);
});

it('throws an infrastructure exception for failed EditaCodigo responses', function () {
    Http::fake([
        'https://host.docker.internal:8443/' => Http::response(['error' => 'failed'], 500),
    ]);

    expect(fn () => (new EditaCodigoWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-003',
    ))->toThrow(EditaCodigoWhatsappMessageSenderException::class);
});

it('does not write the configured token to logs', function () {
    Http::fake([
        'https://host.docker.internal:8443/' => Http::response(['ok' => true], 200),
    ]);

    (new EditaCodigoWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-004',
    );

    Log::shouldHaveReceived('info')
        ->with('whatsapp_reply_sent', Mockery::on(
            fn (array $context): bool => ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'secret-token')
                && $context['http_status'] === 200
        ))
        ->once();
});
