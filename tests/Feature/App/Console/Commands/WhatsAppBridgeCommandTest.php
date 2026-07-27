<?php

use App\Core\Application\Interfaces\Usecase\ProcessWhatsappMessageUsecaseInterface;
use App\Core\Infra\External\PythonWhatsappMessageBridge;
use Mockery\MockInterface;

it('does not start the legacy python bridge when the EditaCodigo HTTP transport is configured', function () {
    config(['whatsapp.transport' => 'editacodigo_http']);

    $this->mock(PythonWhatsappMessageBridge::class, function (MockInterface $mock) {
        $mock->shouldReceive('stream')->never();
    });

    $this->artisan('whatsapp:bridge')
        ->expectsOutput('A ponte Python/Selenium do WhatsApp está depreciada e não será iniciada.')
        ->assertSuccessful();
});

it('keeps the legacy python bridge fallback functional when explicitly configured', function () {
    config(['whatsapp.transport' => 'python_bridge']);

    $this->mock(ProcessWhatsappMessageUsecaseInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('__invoke')->never();
    });

    $this->mock(PythonWhatsappMessageBridge::class, function (MockInterface $mock) {
        $mock->shouldReceive('stream')
            ->once()
            ->andReturn(0);
    });

    $this->artisan('whatsapp:bridge')
        ->expectsOutput('Iniciando ponte Python/PHP legada do WhatsApp...')
        ->assertSuccessful();
});
