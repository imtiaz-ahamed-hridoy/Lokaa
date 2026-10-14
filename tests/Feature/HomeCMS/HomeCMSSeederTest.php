<?php

use App\Models\HomeHeroSection;
use Database\Seeders\HomeCMSSeeder;

it('seeds the hero section with the correct data', function () {
    $this->seed(HomeCMSSeeder::class);

    $hero = HomeHeroSection::first();

    expect($hero)->not->toBeNull();
    expect($hero->badge)->toBe('Local Rentals Made Simple');
    expect($hero->heading)->toBe('Your Local Marketplace for Products & Services');
    expect($hero->description)->toBe('Explore listings from trusted providers, compare options, and connect directly to buy, rent, or hire with confidence.');
    expect($hero->status)->toBe('Active');
    expect($hero->background_image)->toBe('assets/home-cms-hero/hero.png');
});

it('does not duplicate the hero section on re-seed', function () {
    $this->seed(HomeCMSSeeder::class);
    $this->seed(HomeCMSSeeder::class);

    $count = HomeHeroSection::count();

    expect($count)->toBe(1);
});
