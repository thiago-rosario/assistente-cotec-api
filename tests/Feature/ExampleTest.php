<?php

test('the container status page is available', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSeeText('OK, o container esta rodando.')
        ->assertSeeText('A aplicacao esta disponivel em http://localhost:4200.');
});
