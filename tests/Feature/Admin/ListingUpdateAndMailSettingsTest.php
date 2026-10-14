<?php

use App\Mail\OTP;
use App\Models\HomeFeaturedServicesConfig;
use App\Models\HomeHotDealsConfig;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);

    $this->user = User::factory()->create([
        'role' => 'User',
        'email_verified_at' => now(),
    ]);

    $this->listingType = ListingType::create([
        'name' => 'Products',
        'slug' => 'product',
        'status' => ListingType::STATUS_ACTIVE,
    ]);

    $this->listingCategory = ListingCategory::create([
        'listing_type_id' => $this->listingType->id,
        'name' => 'Electronics',
        'slug' => 'electronics',
        'status' => ListingCategory::STATUS_ACTIVE,
    ]);
});

it('updates listing successfully and passes UpdateListingRequest validation', function () {
    $listing = Listing::create([
        'user_id' => $this->user->id,
        'listing_type_id' => $this->listingType->id,
        'listing_category_id' => $this->listingCategory->id,
        'title' => 'Original Title',
        'slug' => 'original-title',
        'description' => 'Original Description for this listing.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 199.99,
        'city' => 'Austin',
        'status' => Listing::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/marketplace/listings/{$listing->id}", [
        'title' => 'Updated Listing Title',
        'description' => 'Updated Description with more than 20 characters.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 249.99,
        'city' => 'Dallas',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.title', 'Updated Listing Title');
    $this->assertDatabaseHas('listings', [
        'id' => $listing->id,
        'title' => 'Updated Listing Title',
        'price' => 249.99,
        'city' => 'Dallas',
    ]);
});

it('rejects duplicate uploaded images in UpdateListingRequest', function () {
    $listing = Listing::create([
        'user_id' => $this->user->id,
        'listing_type_id' => $this->listingType->id,
        'listing_category_id' => $this->listingCategory->id,
        'title' => 'Image Test Listing',
        'slug' => 'image-test-listing',
        'description' => 'Testing duplicate image validation.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 99.99,
        'city' => 'Austin',
        'status' => Listing::STATUS_ACTIVE,
    ]);

    $image1 = UploadedFile::fake()->image('photo1.jpg');
    $image2 = UploadedFile::fake()->image('photo1.jpg'); // Same content

    $response = $this->actingAs($this->user, 'sanctum')->postJson("/api/marketplace/listings/{$listing->id}", [
        '_method' => 'put',
        'title' => 'Updated Image Test',
        'media' => [$image1, $image2, UploadedFile::fake()->image('photo3.jpg'), UploadedFile::fake()->image('photo4.jpg')],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['media']);
});

it('does not leak mail password in mail settings edit page', function () {
    $response = $this->actingAs($this->admin)->get(route('mail.edit'));

    $response->assertStatus(200);
    // Password must be empty string in props
    $response->assertInertia(fn ($page) => $page
        ->component('settings/mail')
        ->where('mailConfig.MAIL_PASSWORD', '')
    );
});

it('sends test email via mail.test route', function () {
    Mail::fake();

    $response = $this->actingAs($this->admin)->post(route('mail.test'), [
        'test_email' => 'recipient@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'mail-test-success');

    Mail::assertSent(OTP::class, function ($mail) {
        return $mail->hasTo('recipient@example.com');
    });
});

it('clears explore categories cache when category is updated or created', function () {
    Cache::put('home_explore_categories', ['stale' => 'data'], 3600);
    Cache::put('home_page_all', ['stale' => 'home'], 3600);

    expect(Cache::has('home_explore_categories'))->toBeTrue();
    expect(Cache::has('home_page_all'))->toBeTrue();

    $this->actingAs($this->admin)->post(route('listing-category.store'), [
        'listing_type_id' => $this->listingType->id,
        'name' => 'New Cache Test Category',
        'status' => 'Active',
    ]);

    expect(Cache::has('home_explore_categories'))->toBeFalse();
    expect(Cache::has('home_page_all'))->toBeFalse();
});

it('supports manual listing source ordering in hot deals and featured services without sql error', function () {
    $listing1 = Listing::create([
        'user_id' => $this->user->id,
        'listing_type_id' => $this->listingType->id,
        'listing_category_id' => $this->listingCategory->id,
        'title' => 'Listing 1',
        'slug' => 'listing-1',
        'description' => 'Listing 1 description.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 50,
        'city' => 'Austin',
        'status' => Listing::STATUS_ACTIVE,
    ]);

    $listing2 = Listing::create([
        'user_id' => $this->user->id,
        'listing_type_id' => $this->listingType->id,
        'listing_category_id' => $this->listingCategory->id,
        'title' => 'Listing 2',
        'slug' => 'listing-2',
        'description' => 'Listing 2 description.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 100,
        'city' => 'Dallas',
        'status' => Listing::STATUS_ACTIVE,
    ]);

    $hotDeal = HomeHotDealsConfig::create([
        'section_title' => 'Hot Deals',
        'listing_source' => 'manual',
        'manual_listing_ids' => [$listing2->id, $listing1->id],
        'number_of_listings' => 5,
        'status' => 'Active',
    ]);

    $hotDeal->loadListingsRelation();
    $listings = $hotDeal->getRelation('listings');

    expect($listings)->toHaveCount(2);
    expect($listings->first()->id)->toBe($listing2->id);

    $featured = HomeFeaturedServicesConfig::create([
        'title' => 'Featured Services',
        'listing_source' => 'manual',
        'manual_listing_ids' => [$listing1->id, $listing2->id],
        'number_of_listings' => 5,
        'status' => 'Active',
    ]);

    $featured->loadListingsRelation();
    $featuredListings = $featured->getRelation('listings');

    expect($featuredListings)->toHaveCount(2);
    expect($featuredListings->first()->id)->toBe($listing1->id);
});

it('prevents non-admin users from accessing administrative settings', function () {
    $regularUser = User::factory()->create(['role' => 'User']);

    $response = $this->actingAs($regularUser)->get(route('mail.edit'));
    $response->assertRedirect(route('home'));

    $response = $this->actingAs($regularUser)->get(route('maintenance.show'));
    $response->assertRedirect(route('home'));

    $response = $this->actingAs($regularUser)->get(route('system-settings.edit'));
    $response->assertRedirect(route('home'));
});
