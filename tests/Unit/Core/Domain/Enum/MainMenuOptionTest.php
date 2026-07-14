<?php

use App\Core\Conversation\Enum\ConversationState;
use App\Core\Conversation\Enum\MainMenuOption;

it('resolves menu options from user input', function () {
    expect(MainMenuOption::fromInput('1'))->toBe(MainMenuOption::BuildPanelConsultation)
        ->and(MainMenuOption::fromInput(' 2 '))->toBe(MainMenuOption::TravelReportSubmission)
        ->and(MainMenuOption::fromInput('9'))->toBeNull()
        ->and(MainMenuOption::fromInput('Enviar Relatório de Viagem'))->toBeNull();
});

it('maps menu options to conversation states', function (MainMenuOption $option, ConversationState $state) {
    expect($option->nextState())->toBe($state);
})->with([
    [MainMenuOption::BuildPanelConsultation, ConversationState::BuildPanelConsultation],
    [MainMenuOption::TravelReportSubmission, ConversationState::TravelReportLogin],
    [MainMenuOption::TravelReportConsultation, ConversationState::TravelReportConsultation],
    [MainMenuOption::AssistantInformation, ConversationState::AssistantInformation],
    [MainMenuOption::CloseAttendance, ConversationState::Closed],
]);
