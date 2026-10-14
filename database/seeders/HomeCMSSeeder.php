<?php

namespace Database\Seeders;

use App\Models\HomeCtaBanner;
use App\Models\HomeFeaturedServicesConfig;
use App\Models\HomeHeroSection;
use App\Models\HomeHotDealsConfig;
use App\Models\HomeProcessItem;
use Illuminate\Database\Seeder;

class HomeCMSSeeder extends Seeder
{
    public function run(): void
    {
        // Hero Section (singleton - only one active hero at a time)
        if (HomeHeroSection::count() === 0) {
            HomeHeroSection::create([
                'badge' => 'Local Rentals Made Simple',
                'heading' => 'Your Local Marketplace for Products & Services',
                'description' => 'Explore listings from trusted providers, compare options, and connect directly to buy, rent, or hire with confidence.',
                'background_image' => 'assets/home-cms-hero/hero.png',
                'status' => 'Active',
            ]);
        }

        // Process Items - use firstOrCreate by title to prevent duplicates on re-seed
        $processItems = [
            [
                'icon' => '/assets/home-cms-process/Discover.svg',
                'title' => 'Discover',
                'status' => 'Active',
            ],
            [
                'icon' => '/assets/home-cms-process/Connect.svg',
                'title' => 'Connect',
                'status' => 'Active',
            ],
            [
                'icon' => '/assets/home-cms-process/GetWhatYouNeed.svg',
                'title' => 'Get What You Need',
                'status' => 'Active',
            ],
        ];

        foreach ($processItems as $item) {
            HomeProcessItem::firstOrCreate(['title' => $item['title']], $item);
        }

        // Singleton config sections - only seed if empty
        if (HomeHotDealsConfig::count() === 0) {
            HomeHotDealsConfig::create([
                'section_title' => 'Hot Deals',
                'number_of_listings' => 8,
                'listing_source' => 'newest',
                'listing_category_id' => null,
                'manual_listing_ids' => null,
                'status' => 'Active',
            ]);
        }

        if (HomeFeaturedServicesConfig::count() === 0) {
            HomeFeaturedServicesConfig::create([
                'title' => 'Featured Services',
                'number_of_listings' => 8,
                'listing_source' => 'featured',
                'listing_category_id' => null,
                'manual_listing_ids' => null,
                'status' => 'Active',
            ]);
        }

        if (HomeCtaBanner::count() === 0) {
            HomeCtaBanner::create([
                'background_image' => '/assets/home-cms-cta-banners/cta-banner.png',
                'title' => 'Ready to Get Started?',
                'description' => 'Join thousands of satisfied customers who found their perfect service provider through our platform.',
                'status' => 'Active',
            ]);
        }
    }
}
