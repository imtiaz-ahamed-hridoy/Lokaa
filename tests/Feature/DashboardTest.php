<?php

use App\Models\User;

test('guests are redirected to the home page', function () {
    $this->get('/dashboard')->assertRedirect('/');
});

test('authenticated admin users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create(['role' => 'Admin']));

    $this->get('/dashboard')->assertOk();
});
