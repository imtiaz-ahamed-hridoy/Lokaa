<?php

use App\Models\DynamicPage;
use App\Models\HomeCtaBanner;
use App\Models\HomeHeroSection;
use App\Models\HomeProcessItem;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();

    $this->user = User::factory()->create([
        'status' => 'Active',
        'email_verified_at' => now(),
    ]);

    $this->hero = HomeHeroSection::create([
        'badge' => 'Top Rated Marketplace',
        'heading' => 'Find Everything You Need In One Place',
        'description' => 'The leading local platform for goods, services, and deals.',
        'status' => 'Active',
    ]);

    $this->process = HomeProcessItem::create([
        'step_number' => 1,
        'title' => 'Search & Discover',
        'description' => 'Find items from trusted community sellers.',
        'order' => 1,
        'status' => 'Active',
    ]);

    $this->cta = HomeCtaBanner::create([
        'title' => 'Start Selling Today',
        'description' => 'Join thousands of successful merchants on Lokaa.',
        'button_text' => 'Get Started',
        'button_url' => '/register',
        'status' => 'Active',
    ]);

    $this->listingType = ListingType::create([
        'name' => 'Services',
        'slug' => 'services',
        'status' => 'Active',
    ]);

    $this->category = ListingCategory::create([
        'listing_type_id' => $this->listingType->id,
        'name' => 'Home Repair',
        'slug' => 'home-repair',
        'status' => 'Active',
    ]);

    $this->listing = Listing::create([
        'user_id' => $this->user->id,
        'listing_type_id' => $this->listingType->id,
        'listing_category_id' => $this->category->id,
        'title' => 'Emergency Plumbing and Heating Repairs',
        'slug' => 'emergency-plumbing-and-heating-repairs',
        'description' => 'Certified plumbing technicians available 24/7.',
        'pricing_mode' => Listing::PRICING_FIXED,
        'price' => 85,
        'country' => 'United States',
        'city' => 'Austin',
        'status' => Listing::STATUS_ACTIVE,
    ]);
});

test('GET /api/home returns aggregated home CMS data', function () {
    $response = $this->getJson('/api/home');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
    expect($response->json('data.hero.heading'))->toBe('Find Everything You Need In One Place');
    expect($response->json('data.process'))->not->toBeEmpty();
    expect($response->json('data.cta_banner.title'))->toBe('Start Selling Today');
});

test('individual Home CMS endpoints return active content', function () {
    $hero = $this->getJson('/api/home/hero');
    $hero->assertStatus(200);
    expect($hero->json('data.badge'))->toBe('Top Rated Marketplace');

    $process = $this->getJson('/api/home/process');
    $process->assertStatus(200);
    expect($process->json('data'))->toHaveCount(1);

    $categories = $this->getJson('/api/home/explore-categories');
    $categories->assertStatus(200);
    expect($categories->json('data'))->toHaveCount(1);

    $cta = $this->getJson('/api/home/cta-banner');
    $cta->assertStatus(200);
    expect($cta->json('data.title'))->toBe('Start Selling Today');
});

test('hero search endpoint finds listings by keyword and city', function () {
    $response = $this->getJson('/api/search?keyword=Plumbing&location=Austin');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
    expect($response->json('data.listings'))->not->toBeEmpty();
    expect($response->json('data.listings.0.title'))->toContain('Plumbing');
});

test('dynamic pages privacy policy and terms of service return correct data', function () {
    DynamicPage::create([
        'page_title' => 'Privacy Policy',
        'page_slug' => 'privacy-policy',
        'page_content' => '<p>Lokaa respects user data privacy.</p>',
        'status' => 'Active',
    ]);

    DynamicPage::create([
        'page_title' => 'Terms of Service',
        'page_slug' => 'terms-of-service',
        'page_content' => '<p>Terms governing marketplace usage.</p>',
        'status' => 'Active',
    ]);

    $privacy = $this->getJson('/api/privacy-policy');
    $privacy->assertStatus(200);
    expect($privacy->json('data.page_title'))->toBe('Privacy Policy');

    $terms = $this->getJson('/api/terms-of-service');
    $terms->assertStatus(200);
    expect($terms->json('data.page_title'))->toBe('Terms of Service');
});

test('contact form endpoint accepts valid submissions', function () {
    $response = $this->postJson('/api/contact', [
        'name' => 'Jane Contact',
        'email' => 'jane@example.com',
        'phone' => '+15551234567',
        'subject' => 'Inquiry about marketplace features',
        'message' => 'I would like more information on verified seller accounts.',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
    ]);
    $this->assertDatabaseHas('contacts', [
        'email' => 'jane@example.com',
        'name' => 'Jane Contact',
    ]);
});
