<?php

use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Resolver\MessageIntentResolver;
use App\Core\Enum\WhatsappMenuOption;

it('extracts menu options from message content', function (string $content, WhatsappMenuOption $option) {
    expect((new MessageIntentResolver)->menuOption(new MessageEntity($content)))->toBe($option);
})->with([
    ['1', WhatsappMenuOption::BuildPanel],
    ['2 - Consultar relatório', WhatsappMenuOption::TechnicalInspectionReport],
    ['3.', WhatsappMenuOption::AssistantInfo],
    ['0', WhatsappMenuOption::End],
    ['9', WhatsappMenuOption::Invalid],
]);

it('does not treat arbitrary text as a menu option', function () {
    expect((new MessageIntentResolver)->menuOption(new MessageEntity('Quero consultar Antas')))->toBeNull();
});

it('identifies main menu requests after normalization', function () {
    expect((new MessageIntentResolver)->isMainMenuRequest(new MessageEntity(' Opções! ')))->toBeTrue()
        ->and((new MessageIntentResolver)->isMainMenuRequest(new MessageEntity('consultar obras')))->toBeFalse();
});
