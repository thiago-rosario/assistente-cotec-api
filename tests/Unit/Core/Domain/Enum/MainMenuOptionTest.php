<?php

use App\Core\Conversation\Enum\ConversationStateEnum;
use App\Core\Conversation\Enum\MainMenuOptionEnum;

it('resolves menu options from user input', function () {
    expect(MainMenuOptionEnum::fromInput('1'))->toBe(MainMenuOptionEnum::BuildPanelConsultation)
        ->and(MainMenuOptionEnum::fromInput(' 2 '))->toBe(MainMenuOptionEnum::TravelReportSubmission)
        ->and(MainMenuOptionEnum::fromInput('9'))->toBeNull()
        ->and(MainMenuOptionEnum::fromInput('Enviar Relatório de Viagem'))->toBeNull();
});

it('maps menu options to conversation states', function (MainMenuOptionEnum $option, ConversationStateEnum $state) {
    expect($option->nextState())->toBe($state);
})->with([
    [MainMenuOptionEnum::BuildPanelConsultation, ConversationStateEnum::BuildPanelConsultation],
    [MainMenuOptionEnum::TravelReportSubmission, ConversationStateEnum::TravelReportLogin],
    [MainMenuOptionEnum::TravelReportConsultation, ConversationStateEnum::TravelReportConsultation],
    [MainMenuOptionEnum::AssistantInformation, ConversationStateEnum::AssistantInformation],
    [MainMenuOptionEnum::CloseAttendance, ConversationStateEnum::Closed],
]);
