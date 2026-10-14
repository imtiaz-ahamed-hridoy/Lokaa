<?php

it('redirects to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
