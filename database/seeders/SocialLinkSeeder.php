<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SocialLink::create([
            'facebook_link' => null,
            'instagram_link' => null,
            'twitter_link' => null,
            'tiktok_link' => null,
            'linkedin_link' => null,
            'github_link' => null,
            'youtube_link' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
