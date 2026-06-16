<?php

use App\Core\Infra\Message\WhatsappIntentLabel;

it('uses painel de obras as technical notebook label', function () {
    expect((new WhatsappIntentLabel)->for('search_technical_notebook'))->toBe('Painel de Obras');
});
