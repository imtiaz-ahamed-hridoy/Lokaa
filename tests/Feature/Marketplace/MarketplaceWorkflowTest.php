<?php

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seller = User::factory()->create([
        'name' => 'Seller User',
        'email' => 'seller@example.com',
        'status' => 'Active',
        'email_verified_at' => now(),
    ]);

    $this->buyer = User::factory()->create([
        'name' => 'Buyer User',
        'email' => 'buyer@example.com',
        'status' => 'Active',
        'email_verified_at' => now(),
    ]);

    $this->listingType = ListingType::create([
        'name' => 'Vehicles',
        'slug' => 'vehicles',
        'status' => ListingType::STATUS_ACTIVE,
    ]);

    $this->category = ListingCategory::create([
        'listing_type_id' => $this->listingType->id,
        'name' => 'Sedan',
        'slug' => 'sedan',
        'status' => ListingCategory::STATUS_ACTIVE,
    ]);

    $this->listing = Listing::create([
        'user_id' => $this->seller->id,
        'listing_type_id' => $this->listingType->id,
        'listing_category_id' => $this->category->id,
        'title' => '2024 Modern Sedan in Mint Condition',
        'slug' => '2024-modern-sedan-in-mint-condition',
        'description' => 'A wonderful vehicle with low mileage and complete service history.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 25000,
        'country' => 'United States',
        'city' => 'Austin',
        'area' => 'Downtown',
        'status' => Listing::STATUS_ACTIVE,
    ]);
});

test('public user can list active marketplace items and lookups', function () {
    $response = $this->getJson('/api/marketplace');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
    expect($response->json('data.listings'))->not->toBeEmpty();
    expect($response->json('data.listing_types'))->not->toBeEmpty();
    expect($response->json('data.categories'))->not->toBeEmpty();
});

test('public user can view single active listing detail', function () {
    $response = $this->getJson("/api/marketplace/{$this->listing->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
    expect($response->json('data.title'))->toBe('2024 Modern Sedan in Mint Condition');
    expect((float) $response->json('data.price'))->toBe(25000.0);
});

test('authenticated user can toggle wishlist on a listing', function () {
    // Toggle on (favorite)
    $response1 = $this->actingAs($this->buyer, 'sanctum')->postJson("/api/wishlist/{$this->listing->id}");
    $response1->assertStatus(200);
    $response1->assertJson([
        'success' => true,
    ]);
    expect($response1->json('data.is_wishlisted'))->toBeTrue();
    $this->assertDatabaseHas('listing_favorites', [
        'user_id' => $this->buyer->id,
        'listing_id' => $this->listing->id,
    ]);

    // Check wishlist list
    $wishlist = $this->actingAs($this->buyer, 'sanctum')->getJson('/api/wishlist');
    $wishlist->assertStatus(200);
    expect($wishlist->json('data'))->toHaveCount(1);

    // Toggle off (unfavorite)
    $response2 = $this->actingAs($this->buyer, 'sanctum')->postJson("/api/wishlist/{$this->listing->id}");
    $response2->assertStatus(200);
    expect($response2->json('data.is_wishlisted'))->toBeFalse();
    $this->assertDatabaseMissing('listing_favorites', [
        'user_id' => $this->buyer->id,
        'listing_id' => $this->listing->id,
    ]);
});

test('authenticated user can post and retrieve a review for seller', function () {
    $response = $this->actingAs($this->buyer, 'sanctum')->postJson('/api/reviews', [
        'listing_id' => $this->listing->id,
        'rating' => 5,
        'description' => 'Excellent seller! Very smooth transaction.',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Review submitted successfully.',
    ]);
    expect($response->json('data.rating'))->toBe(5);

    $this->assertDatabaseHas('user_reviews', [
        'reviewer_id' => $this->buyer->id,
        'reviewee_id' => $this->seller->id,
        'rating' => 5,
    ]);

    // Public list reviews for seller
    $list = $this->getJson("/api/users/{$this->seller->id}/reviews");
    $list->assertStatus(200);
    expect($list->json('data'))->toHaveCount(1);

    // Review summary
    $summary = $this->getJson("/api/reviews/summary/{$this->seller->id}");
    $summary->assertStatus(200);
    expect($summary->json('data.total_reviews'))->toBe(1);
    expect((float) $summary->json('data.average_rating'))->toBe(5.0);
});

test('seller can create and update their listing via authenticated marketplace endpoints', function () {
    $createResponse = $this->actingAs($this->seller, 'sanctum')->postJson('/api/marketplace/listings', [
        'listing_type_id' => $this->listingType->id,
        'listing_category_id' => $this->category->id,
        'title' => 'New Camera Gear For Photography',
        'description' => 'Professional DSLR camera with prime lenses and accessories included.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 1200,
        'city' => 'Houston',
        'media' => [
            UploadedFile::fake()->image('img1.jpg', 100, 100),
            UploadedFile::fake()->image('img2.jpg', 120, 120),
            UploadedFile::fake()->image('img3.jpg', 140, 140),
            UploadedFile::fake()->image('img4.jpg', 160, 160),
        ],
    ]);

    $createResponse->assertStatus(201);
    $createResponse->assertJson(['success' => true]);
    $newListingId = $createResponse->json('data.id');

    // Update the listing
    $updateResponse = $this->actingAs($this->seller, 'sanctum')->putJson("/api/marketplace/listings/{$newListingId}", [
        'title' => 'New Camera Gear For Photography - Reduced Price',
        'description' => 'Professional DSLR camera with prime lenses and accessories included.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 1100,
        'city' => 'Houston',
    ]);

    $updateResponse->assertStatus(200);
    expect((float) $updateResponse->json('data.price'))->toBe(1100.0);

    // Another user cannot update seller's listing
    $forbiddenResponse = $this->actingAs($this->buyer, 'sanctum')->putJson("/api/marketplace/listings/{$newListingId}", [
        'title' => 'Hacked title',
        'description' => 'Should fail with 403',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 10,
    ]);
    $forbiddenResponse->assertStatus(403);
});

test('authenticated user can view marketplace seller dashboard metrics', function () {
    $response = $this->actingAs($this->seller, 'sanctum')->getJson('/api/marketplace/dashboard');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'greeting',
            'stats' => [
                'active_listings',
                'total_listings',
                'pending_listings',
                'draft_listings',
            ],
            'recent_listings',
        ],
    ]);
    expect($response->json('data.stats.total_listings'))->toBe(1);
});
