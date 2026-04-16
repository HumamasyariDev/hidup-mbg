<?php

declare(strict_types=1);

test('health endpoint returns ok', function (): void {
    $response = $this->getJson('/api/health');

    $response->assertOk()
        ->assertJsonStructure(['status', 'timestamp'])
        ->assertJson(['status' => 'ok']);
});

test('login requires email and password', function (): void {
    $response = $this->postJson('/api/auth/login', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('unauthenticated request to /me returns 401', function (): void {
    $response = $this->getJson('/api/me');

    $response->assertUnauthorized();
});

test('unauthenticated request to CRUD endpoints returns 401', function (): void {
    $this->getJson('/api/admins')->assertUnauthorized();
    $this->getJson('/api/sppg-providers')->assertUnauthorized();
    $this->getJson('/api/schools')->assertUnauthorized();
    $this->getJson('/api/mbg-menus')->assertUnauthorized();
});
