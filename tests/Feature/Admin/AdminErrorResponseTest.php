<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Integration tests for admin backend error responses.
 *
 * Admin routes (in routes/backend.php) are loaded with ['web', 'admin'] middleware.
 * AdminMiddleware checks: Auth::check() && Auth::user()->role == 'Admin'.
 * Non-admin users are redirected to the home page.
 * Route model binding throws 404 for non-existent resources.
 *
 * NOTE: Several admin controllers (ListingType, ListingCategory, User, DynamicPage)
 * do NOT define a show() method, so GET /{resource}/99999 throws 500.
 * Instead we test 404 via the edit() route: GET /{resource}/99999/edit.
 */

// ---------------------------------------------------------------------------
//  403 FORBIDDEN  —  Non-admin users redirected from admin routes
// ---------------------------------------------------------------------------

test('regular user accessing admin dashboard is redirected (403)', function () {
    $user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('home'));
});

test('regular user accessing admin contact list is redirected (403)', function () {
    $user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/contact');

    $response->assertRedirect(route('home'));
});

test('regular user accessing admin listing types is redirected (403)', function () {
    $user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/listing-type');

    $response->assertRedirect(route('home'));
});

test('regular user accessing admin users page is redirected (403)', function () {
    $user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/user');

    $response->assertRedirect(route('home'));
});

test('regular user accessing admin dynamic pages is redirected (403)', function () {
    $user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dynamic_page');

    $response->assertRedirect(route('home'));
});

test('regular user accessing admin newsletters is redirected (403)', function () {
    $user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/newsletter');

    $response->assertRedirect(route('home'));
});

// ---------------------------------------------------------------------------
//  404 NOT FOUND  —  Admin user accessing non-existent resources
//  (uses edit route because some controllers lack a show() method)
// ---------------------------------------------------------------------------

test('admin accessing non-existent contact returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/contact/99999');

    $response->assertStatus(404);
});

test('admin accessing non-existent dynamic page edit returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/dynamic_page/99999/edit');

    $response->assertStatus(404);
});

test('admin accessing non-existent listing type edit returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/listing-type/99999/edit');

    $response->assertStatus(404);
});

test('admin accessing non-existent listing category edit returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/listing-category/99999/edit');

    $response->assertStatus(404);
});

test('admin accessing non-existent user returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/user/99999');

    $response->assertStatus(404);
});

test('admin accessing non-existent listing type returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/listing-type/99999');

    $response->assertStatus(404);
});

test('admin accessing non-existent listing category returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/listing-category/99999');

    $response->assertStatus(404);
});

test('admin accessing non-existent dynamic page returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/dynamic_page/99999');

    $response->assertStatus(404);
});

test('admin accessing non-existent listing returns 404', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/listing/99999');

    $response->assertStatus(404);
});

// ---------------------------------------------------------------------------
//  429 TOO MANY REQUESTS  —  Rate limit exceeded on web routes
// ---------------------------------------------------------------------------

test('exceeding rate limit on web route returns 429', function () {
    // Register a test route with web middleware and a strict throttle (1 req/min)
    Route::get('/_test-web-429', function () {
        return response('OK', 200);
    })->middleware('web')->middleware('throttle:1,1');

    // First request should succeed
    $first = $this->get('/_test-web-429');
    $first->assertStatus(200);
    expect($first->content())->toBe('OK');

    // Second request should exceed the throttle and return 429 (HTML error page)
    $second = $this->get('/_test-web-429');
    $second->assertStatus(429);
});

// ---------------------------------------------------------------------------
//  SUCCESS  —  Admin user can access admin routes
// ---------------------------------------------------------------------------

test('admin user can access dashboard successfully', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertStatus(200);
});

test('admin user can access contact list successfully', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/contact');

    $response->assertStatus(200);
});

test('regular user accessing system-log is redirected', function () {
    $user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/system-log');

    $response->assertRedirect(route('home'));
});

test('admin user can access system-log successfully', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/system-log');

    $response->assertStatus(200);
});

test('admin user can access all admin sections successfully', function () {
    $admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $routes = [
        '/user',
        '/listing-type',
        '/listing-category',
        '/listing',
        '/review',
        '/dynamic_page',
        '/newsletter',
        '/home-cms/hero',
        '/home-cms/process',
        '/home-cms/hot-deals',
        '/home-cms/featured-services',
        '/home-cms/cta-banner',
        '/settings/system-settings',
        '/settings/social-links',
        '/settings/maintenance',
    ];

    foreach ($routes as $route) {
        $response = $this->actingAs($admin)->get($route);
        $response->assertStatus(200);
    }
});
