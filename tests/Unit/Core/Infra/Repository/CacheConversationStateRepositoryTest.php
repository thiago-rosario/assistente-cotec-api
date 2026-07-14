<?php

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Conversation\Enum\ConversationState;
use App\Core\Conversation\Infra\Repository\CacheConversationStateRepository;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

it('persists conversation state by customer identifier', function () {
    $repository = new CacheConversationStateRepository(Cache::store('array'));
    $input = new ReceivedMessageInputDTO(message: 'Olá', phone: '5571999999999');

    $repository->put($input, ConversationState::MainMenu);

    expect($repository->get($input))->toBe(ConversationState::MainMenu);

    $repository->forget($input);

    expect($repository->get($input))->toBeNull();
});
