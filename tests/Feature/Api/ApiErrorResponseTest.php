<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Integration tests for standardized API error response formats.
 *
 * Every error response should follow:
 *   { success: false, message: "...", errors: null|{field: [msgs]} }
 */

// ---------------------------------------------------------------------------
//  401 UNAUTHORIZED  —  "Authentication required."
// ---------------------------------------------------------------------------

test('GET /api/user without token returns 401 with standardized format', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'Authentication required.',
        'errors' => null,
    ]);
});

test('POST /api/logout without token returns 401 with standardized format', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'Authentication required.',
        'errors' => null,
    ]);
});

test('GET /api/marketplace/dashboard without token returns 401', function () {
    $response = $this->getJson('/api/marketplace/dashboard');

    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'Authentication required.',
        'errors' => null,
    ]);
});

test('POST /api/user/profile/update without token returns 401', function () {
    $response = $this->postJson('/api/user/profile/update');

    $response->assertStatus(401);
    $response->assertJsonStructure([
        'success',
        'message',
        'errors',
    ]);
});

test('POST /api/login with wrong credentials returns 401', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'The provided credentials do not match our records.',
        'errors' => [
            'email' => ['The provided credentials do not match our records.'],
        ],
    ]);
});

// ---------------------------------------------------------------------------
//  403 FORBIDDEN  —  "You do not have permission to perform this action."
// ---------------------------------------------------------------------------

test('login with unverified email returns 403', function () {
    $user = User::factory()->unverified()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
        'message' => 'Email not verified.',
        'errors' => null,
    ]);
});

// ---------------------------------------------------------------------------
//  404 NOT FOUND  —  "Resource not found."
// ---------------------------------------------------------------------------

test('GET /api/marketplace/99999 with non-existent listing returns 404', function () {
    $response = $this->getJson('/api/marketplace/99999');

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'message' => 'Resource not found.',
        'errors' => null,
    ]);
});

test('GET /api/non-existent-route returns 404', function () {
    $response = $this->getJson('/api/non-existent-route');

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'message' => 'Resource not found.',
        'errors' => null,
    ]);
});

test('POST /api/reset-password with invalid token returns 404', function () {
    $response = $this->postJson('/api/reset-password', [
        'token' => 'invalid-token-that-does-not-exist',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
        'message' => 'Invalid Token',
        'errors' => null,
    ]);
});

// ---------------------------------------------------------------------------
//  422 VALIDATION FAILED  —  "Validation failed."
// ---------------------------------------------------------------------------

test('POST /api/register with missing required fields returns 422', function () {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Validation failed.',
    ]);

    // Should contain field-specific error arrays
    $json = $response->json();
    expect($json['errors'])->toBeArray();
    expect($json['errors'])->toHaveKeys(['name', 'email', 'password']);
});

test('POST /api/login with missing fields returns 422', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Validation failed.',
    ]);

    $json = $response->json();
    expect($json['errors'])->toBeArray();
    expect($json['errors'])->toHaveKeys(['email', 'password']);
});

test('POST /api/register with invalid agreeToTerms returns 422', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'agreeToTerms' => false,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Validation failed.',
    ]);
});

test('GET /api/search without keyword or location returns 422', function () {
    $response = $this->getJson('/api/search');

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Validation failed.',
    ]);
});

// ---------------------------------------------------------------------------
//  429 TOO MANY REQUESTS  —  "Too many requests. Please try again later."
// ---------------------------------------------------------------------------

test('exceeding rate limit returns 429 with standardized format', function () {
    $originalEnv = app()->environment();
    $this->app['env'] = 'production';

    // Register a test route with a strict throttle (1 request / minute)
    Route::get('/api/_test-429', function () {
        return response()->json(['success' => true, 'message' => 'OK']);
    })->middleware('api')->middleware('throttle:1,1');

    // First request should succeed
    $first = $this->getJson('/api/_test-429');
    $first->assertStatus(200);

    // Second request should exceed the throttle and return 429
    $second = $this->getJson('/api/_test-429');

    $this->app['env'] = $originalEnv;

    $second->assertStatus(429);
    $second->assertJson([
        'success' => false,
        'message' => 'Too many requests. Please try again later.',
        'errors' => null,
    ]);
});

// ---------------------------------------------------------------------------
//  500 INTERNAL SERVER ERROR  —  "An unexpected server error occurred."
// ---------------------------------------------------------------------------

test('unhandled exception returns 500 with safe standardized message', function () {
    // Force production mode so the exception handler returns safe messages
    // instead of the real exception message
    $originalEnv = app()->environment();
    $this->app['env'] = 'production';

    Route::get('/api/_test-500', function () {
        throw new Exception('Internal server failure');
    })->middleware('api');

    $response = $this->getJson('/api/_test-500');

    // Restore the original environment
    $this->app['env'] = $originalEnv;

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'An unexpected server error occurred.',
        'errors' => null,
    ]);
});

// ---------------------------------------------------------------------------
//  SUCCESS RESPONSE FORMATS  —  verify the positive case also matches spec
// ---------------------------------------------------------------------------

test('successful login returns 200 with correct format', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'token_type',
            'token',
            'user',
        ],
    ]);
    expect($response->json('success'))->toBeTrue();
});

test('successful resource creation returns 201 with correct format', function () {
    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'test-subscriber-201@example.com',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Newsletter subscribed successfully.',
    ]);
    expect($response->json('data'))->toHaveKey('email');
});
