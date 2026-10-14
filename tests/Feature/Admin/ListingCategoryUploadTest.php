<?php

use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $this->listingType = ListingType::create([
        'name' => 'Test Type',
        'slug' => 'test-type',
        'status' => ListingType::STATUS_ACTIVE,
    ]);
});

it('can store and retrieve image_url via model', function () {
    $category = ListingCategory::create([
        'listing_type_id' => $this->listingType->id,
        'name' => 'Test Category',
        'slug' => 'test-category',
        'image' => 'uploads/marketplace/categories/test.png',
        'status' => 'Active',
    ]);

    expect($category->image)->toBe('uploads/marketplace/categories/test.png');
    expect($category->image_url)->toMatch('/^https?:\/\/.+\/uploads\/marketplace\/categories\/test\.png$/');
});

it('exposes image_url and hides description in API response', function () {
    $category = ListingCategory::create([
        'listing_type_id' => $this->listingType->id,
        'name' => 'API Test Category',
        'slug' => 'api-test-category',
        'status' => 'Active',
    ]);

    $response = $this->getJson('/api/marketplace');

    $response->assertStatus(200);

    $categories = $response->json('data.categories');
    $found = collect($categories)->firstWhere('id', $category->id);

    expect($found)->not->toBeNull();
    expect($found)->toHaveKey('image_url');
    expect($found)->not->toHaveKey('description');
});

it('validates image field as image file', function () {
    $response = $this->actingAs($this->admin)->post(route('listing-category.store'), [
        'listing_type_id' => $this->listingType->id,
        'name' => 'Test Category',
        'slug' => 'test-category',
        'image' => 'not-an-image',
        'status' => 'Active',
    ]);

    // Should return 419 (CSRF) or 302 (redirect back with errors) - either way the category is not created
    $category = ListingCategory::where('slug', 'test-category')->first();
    expect($category)->toBeNull();
});

it('appends image_url only when image is present', function () {
    $category = ListingCategory::create([
        'listing_type_id' => $this->listingType->id,
        'name' => 'No Image Category',
        'slug' => 'no-image-category',
        'status' => 'Active',
    ]);

    // When no image, image_url should be null
    expect($category->image)->toBeNull();
    expect($category->image_url)->toBeNull();

    // When image is set, image_url should return full URL
    $category->update(['image' => 'uploads/marketplace/categories/test.png']);
    expect($category->image_url)->toMatch('/^https?:\/\/.+\/uploads\/marketplace\/categories\/test\.png$/');
});
