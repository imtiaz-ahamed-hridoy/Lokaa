<?php

use App\Models\HomeHeroSection;
use App\Models\User;
use App\Traits\FileManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'Admin',
        'email_verified_at' => now(),
    ]);
});

it('can store a hero section with background image and return full image_url', function () {
    $file = UploadedFile::fake()->image('hero-bg.png', 1920, 1080);

    $hero = HomeHeroSection::create([
        'badge' => 'Test Badge',
        'heading' => 'Test Heading',
        'description' => 'Test Description',
        'background_image' => null,
        'status' => 'Active',
    ]);

    expect($hero->background_image)->toBeNull();
    expect($hero->background_image_url)->toBeNull();

    // Simulate file upload
    $paths = (new class
    {
        use FileManager;

        public function run($file)
        {
            return $this->uploadToPublic($file, 'uploads/home/hero');
        }
    })->run($file);

    $hero->update(['background_image' => $paths[0]]);
    $hero->refresh();

    expect($hero->background_image)->toMatch('/^uploads\/home\/hero\/.+\.png$/');
    expect($hero->background_image_url)->toMatch('/^https?:\/\/.+\/uploads\/home\/hero\/.+\.png$/');
});

it('returns null background_image_url when no image is set', function () {
    $hero = HomeHeroSection::create([
        'badge' => 'No Image Badge',
        'heading' => 'No Image Heading',
        'description' => 'No description',
        'status' => 'Active',
    ]);

    expect($hero->background_image)->toBeNull();
    expect($hero->background_image_url)->toBeNull();
});

it('replaces old background image on update', function () {
    $file1 = UploadedFile::fake()->image('old-bg.png', 100, 100);

    $paths1 = (new class
    {
        use FileManager;

        public function run($file)
        {
            return $this->uploadToPublic($file, 'uploads/home/hero');
        }
    })->run($file1);

    $hero = HomeHeroSection::create([
        'badge' => 'Update Test',
        'heading' => 'Update Heading',
        'description' => 'Update description',
        'background_image' => $paths1[0],
        'status' => 'Active',
    ]);

    $oldUrl = $hero->background_image_url;
    expect($oldUrl)->toMatch('/^https?:\/\/.+\/uploads\/home\/hero\/.+\.png$/');

    // Upload new image
    $file2 = UploadedFile::fake()->image('new-bg.png', 100, 100);
    $paths2 = (new class
    {
        use FileManager;

        public function run($file)
        {
            return $this->uploadToPublic($file, 'uploads/home/hero');
        }
    })->run($file2);

    $hero->update(['background_image' => $paths2[0]]);
    $hero->refresh();

    // New URL should be different from old URL
    expect($hero->background_image_url)->not->toBe($oldUrl);
    expect($hero->background_image_url)->toMatch('/^https?:\/\/.+\/uploads\/home\/hero\/.+\.png$/');
});

it('returns background_image_url in API response and hides background_image', function () {
    $hero = HomeHeroSection::create([
        'badge' => 'API Test',
        'heading' => 'API Heading',
        'description' => 'API description',
        'background_image' => 'uploads/home/hero/test.png',
        'status' => 'Active',
    ]);

    $response = $this->getJson('/api/home/hero');

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data)->toHaveKey('background_image_url');
    expect($data)->not->toHaveKey('background_image');
    expect($data)->not->toHaveKey('search_placeholder_1');
    expect($data)->not->toHaveKey('search_placeholder_2');
    expect($data)->not->toHaveKey('search_button_text');
    expect($data['background_image_url'])->toMatch('/^https?:\/\/.+\/uploads\/home\/hero\/test\.png$/');
});
