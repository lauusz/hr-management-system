<?php

test('the application redirects to login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
