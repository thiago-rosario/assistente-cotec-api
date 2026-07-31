<?php

use App\Core\Domain\Entity\MessageEntity;

it('identifies messages without text content', function () {
    expect((new MessageEntity('   '))->hasTextContent())->toBeFalse();
});

it('normalizes phone numbers for conversation state lookup', function () {
    expect(new MessageEntity('Olá', ' 5571999999999 '))->normalizedPhone()->toBe('5571999999999')
        ->and(new MessageEntity('Olá', '   '))->normalizedPhone()->toBeNull();
});

it('normalizes content for downstream intent resolution', function () {
    expect(new MessageEntity(' Opções! '))->normalizedContent()->toBe('opcoes')
        ->and(new MessageEntity('  Olá, MENU!!!  '))->normalizedContent()->toBe('ola menu');
});
