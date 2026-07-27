<?php

use App\Core\Exception\EditaCodigoWhatsappMessageSenderException;
use App\Core\Infra\External\EditaCodigoWhatsappMessageSender;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'services.editacodigo_bot.webhook_url' => 'http://host.docker.internal:5000/webhook',
        'services.editacodigo_bot.user' => 'editacodigo_user',
        'services.editacodigo_bot.token' => 'secret-token',
        'services.editacodigo_bot.timeout' => 15,
        'services.editacodigo_bot.retry_times' => 3,
    ]);

    Http::preventStrayRequests();
    Log::spy();
});

it('sends the EditaCodigo whatsapp message contract with json headers', function () {
    Http::fake([
        'http://host.docker.internal:5000/webhook' => Http::response(['ok' => true], 200),
    ]);

    (new EditaCodigoWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-001',
    );

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'http://host.docker.internal:5000/webhook'
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
        'http://host.docker.internal:5000/webhook' => Http::response(['error' => 'failed'], 500),
    ]);

    expect(fn () => (new EditaCodigoWhatsappMessageSender)->send(
        phone: '5571999999999',
        message: 'Resposta gerada pelo assistente',
        externalId: 'message-003',
    ))->toThrow(EditaCodigoWhatsappMessageSenderException::class);
});

it('does not write the configured token to logs', function () {
    Http::fake([
        'http://host.docker.internal:5000/webhook' => Http::response(['ok' => true], 200),
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
