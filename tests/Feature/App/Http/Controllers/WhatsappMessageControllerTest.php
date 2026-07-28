<?php

use App\Jobs\ProcessIncomingWhatsappMessageJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config(['cache.default' => 'array']);

    Cache::flush();
    Bus::fake();
    Log::spy();
});

it('accepts the canonical EditaCodigo payload and dispatches processing', function () {
    $this->postJson('/api/whatsapp/messages', [
        'customer_contact' => '5571999999999',
        'content' => 'Olá',
        'external_id' => 'editacodigo-001',
        'received_at' => '2026-07-27T14:30:00-03:00',
        'source' => 'editacodigo',
    ])
        ->assertStatus(202)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'accepted' => true,
                'external_id' => 'editacodigo-001',
                'duplicate' => false,
            ],
        ]);

    Bus::assertDispatched(ProcessIncomingWhatsappMessageJob::class, function (ProcessIncomingWhatsappMessageJob $job): bool {
        $payload = $job->payload();

        return $payload['message'] === 'Olá'
            && $payload['phone'] === '5571999999999'
            && $payload['received_at'] === '2026-07-27T14:30:00-03:00'
            && $payload['source'] === 'editacodigo'
            && $payload['external_id'] === 'editacodigo-001';
    });
});

it('accepts the real EditaCodigo payload aliases with numeric timestamp', function () {
    $this->postJson('/api/whatsapp/messages', [
        'telefone' => '5571999999999',
        'texto' => 'Consultar Andaraí',
        'id_mensagem' => 'editacodigo-real-001',
        'timestamp' => 1785162600,
        'source' => 'editacodigo',
    ])
        ->assertStatus(202)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'accepted' => true,
                'external_id' => 'editacodigo-real-001',
                'duplicate' => false,
            ],
        ]);

    Bus::assertDispatched(ProcessIncomingWhatsappMessageJob::class, function (ProcessIncomingWhatsappMessageJob $job): bool {
        $payload = $job->payload();

        return $payload['message'] === 'Consultar Andaraí'
            && $payload['phone'] === '5571999999999'
            && $payload['received_at'] === '1785162600'
            && $payload['source'] === 'editacodigo'
            && $payload['external_id'] === 'editacodigo-real-001';
    });
});

it('keeps accepting legacy aliases for whatsapp payloads', function () {
    $this->postJson('/api/whatsapp/messages', [
        'body' => 'Buscar processo 020.4487.2021.0009714-69',
        'from' => 'whatsapp:+55 (71) 98888-7777',
        'name' => 'Thiago',
        'timestamp' => '2026-07-27T15:00:00-03:00',
        'message_id' => 'legacy-001',
        'provider' => 'legacy-provider',
    ])
        ->assertStatus(202)
        ->assertJson([
            'status' => 'success',
            'data' => [
                'accepted' => true,
                'external_id' => 'legacy-001',
                'duplicate' => false,
            ],
        ]);

    Bus::assertDispatched(ProcessIncomingWhatsappMessageJob::class, function (ProcessIncomingWhatsappMessageJob $job): bool {
        $payload = $job->payload();

        return $payload['message'] === 'Buscar processo 020.4487.2021.0009714-69'
            && $payload['phone'] === '+5571988887777'
            && $payload['sender_name'] === 'Thiago'
            && $payload['received_at'] === '2026-07-27T15:00:00-03:00'
            && $payload['source'] === 'legacy-provider'
            && $payload['external_id'] === 'legacy-001';
    });
});

it('returns jsend validation errors when the whatsapp message content is missing', function () {
    $this->postJson('/api/whatsapp/messages', [
        'customer_contact' => '5571999999999',
    ])
        ->assertUnprocessable()
        ->assertJson([
            'status' => 'fail',
            'data' => [
                'message' => ['The message field is required.'],
            ],
        ]);

    Bus::assertNotDispatched(ProcessIncomingWhatsappMessageJob::class);
});

it('does not dispatch duplicated external ids twice', function () {
    $payload = [
        'customer_contact' => '5571999999999',
        'content' => 'Olá',
        'external_id' => 'duplicated-001',
        'source' => 'editacodigo',
    ];

    $this->postJson('/api/whatsapp/messages', $payload)
        ->assertStatus(202)
        ->assertJsonPath('data.accepted', true)
        ->assertJsonPath('data.duplicate', false);

    $this->postJson('/api/whatsapp/messages', $payload)
        ->assertStatus(202)
        ->assertJsonPath('data.accepted', false)
        ->assertJsonPath('data.duplicate', true);

    Bus::assertDispatchedTimes(ProcessIncomingWhatsappMessageJob::class, 1);
});
