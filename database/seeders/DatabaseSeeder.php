<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->truncateSeededTables();

        $this->call([
            UserSeeder::class,
            SystemSettingSeeder::class,
            DynamicPageSeeder::class,
            SocialLinkSeeder::class,
            MarketplaceSeeder::class,
            HomeCMSSeeder::class,
        ]);
    }

    private function truncateSeededTables(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            foreach ($this->tablesToTruncate() as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        });
    }

    /**
     * Child tables must be truncated before parent tables for databases that
     * still validate references while rebuilding table metadata.
     */
    private function tablesToTruncate(): array
    {
        return [
            'firebase_tokens',
            'personal_access_tokens',
            'notifications',
            'listing_favorites',
            'listing_views',
            'listing_media',
            'listings',
            'listing_categories',
            'listing_types',
            'users',
            'system_settings',
            'dynamic_pages',
            'social_links',
            'home_cta_banners',
            'home_featured_services_configs',
            'home_hot_deals_configs',
            'home_explore_categories',
            'home_process_items',
            'home_hero_sections',
        ];
    }
}
