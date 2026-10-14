<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingFavorite;
use App\Models\ListingMedia;
use App\Models\ListingType;
use App\Models\ListingView;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedListingTypesAndCategories();
        $this->seedTestListings();
    }

    private function seedListingTypesAndCategories(): void
    {
        $categoriesWithImages = [
            'Tools & Equipment' => 'assets/category/tools&equipment.svg',
            'Vehicles' => 'assets/category/vehicles.svg',
            'Event & Wedding' => 'assets/category/event&wedding.svg',
            'Home Gardening' => 'assets/category/homegardening.svg',
            'Property & Spaces' => 'assets/category/property&spaces.svg',
            'Electronics' => 'assets/category/electronics.svg',
        ];

        $types = [
            'Product' => array_keys($categoriesWithImages),
            'Service' => array_keys($categoriesWithImages),
        ];

        foreach ($types as $typeName => $categories) {
            $type = ListingType::updateOrCreate(
                ['slug' => Str::slug($typeName)],
                [
                    'name' => $typeName,
                    'status' => ListingType::STATUS_ACTIVE,
                ]
            );

            foreach ($categories as $categoryName) {
                ListingCategory::updateOrCreate(
                    [
                        'listing_type_id' => $type->id,
                        'slug' => Str::slug($categoryName),
                    ],
                    [
                        'name' => $categoryName,
                        'image' => $categoriesWithImages[$categoryName] ?? null,
                        'status' => ListingCategory::STATUS_ACTIVE,
                    ]
                );
            }
        }
    }

    private function seedTestListings(): void
    {
        $users = User::where('status', 'Active')->get();
        if ($users->isEmpty()) {
            return;
        }

        $types = ListingType::active()->with('categories')->get();
        if ($types->isEmpty()) {
            return;
        }

        $listingsData = $this->getListingsData();
        $createdListings = collect();

        DB::transaction(function () use ($listingsData, $users, $types, &$createdListings): void {
            foreach ($listingsData as $index => $data) {
                $user = $users->random();

                // Match listing to specific type/category based on data or fallback to random
                $type = $types->firstWhere('slug', $data['type_slug'] ?? null) ?? $types->random();
                $category = $type->categories->firstWhere('slug', $data['category_slug'] ?? null)
                    ?? $type->categories->random();

                $listing = Listing::create([
                    'user_id' => $user->id,
                    'listing_type_id' => $type->id,
                    'listing_category_id' => $category->id,
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']).'-'.($index + 1).'-'.Str::random(6),
                    'description' => $data['description'],
                    'pricing_mode' => $data['pricing_mode'],
                    'price' => $data['price'],
                    'country' => $data['country'] ?? 'USA',
                    'city' => $data['city'],
                    'area' => $data['area'] ?? null,
                    'street_address' => $data['street_address'] ?? null,
                    'status' => $data['status'],
                    'is_featured' => $data['is_featured'] ?? false,
                    'is_verified' => $data['is_verified'] ?? false,
                    'published_at' => in_array($data['status'], [Listing::STATUS_ACTIVE]) ? now()->subDays(rand(1, 30)) : null,
                ]);

                // Create placeholder media records with a generic placeholder path
                $mediaCount = rand(2, 5);
                for ($i = 0; $i < $mediaCount; $i++) {
                    ListingMedia::create([
                        'listing_id' => $listing->id,
                        'path' => 'assets/listing/default-listing.jpg',
                    ]);
                }

                // Batch insert views for performance
                $viewCount = rand(5, 50);
                $viewInserts = [];
                for ($v = 0; $v < $viewCount; $v++) {
                    $viewInserts[] = [
                        'listing_id' => $listing->id,
                        'user_id' => $users->random()->id,
                        'ip_address' => long2ip(rand(0, 4294967295)),
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                        'updated_at' => now(),
                    ];
                }
                ListingView::insert($viewInserts);

                $createdListings->push($listing);
            }
        });

        // Add some favorites (cross-user wishlists)
        if ($createdListings->isNotEmpty() && $users->count() > 1) {
            $alreadyFavorited = [];
            $favoriteInserts = [];
            $userIds = $users->pluck('id')->toArray();

            foreach ($createdListings->shuffle()->take((int) ceil($createdListings->count() * 0.6)) as $listing) {
                $otherUserIds = array_values(array_filter(
                    $userIds,
                    fn (int $uid): bool => $uid !== $listing->user_id
                ));

                $maxFav = count($otherUserIds);
                if ($maxFav === 0) {
                    continue;
                }

                $favCount = min(rand(1, min(3, $maxFav)), $maxFav);

                // Get random user IDs (handles single or multiple)
                $randomIds = $favCount === 1
                    ? [$otherUserIds[array_rand($otherUserIds)]]
                    : array_map(fn ($k) => $otherUserIds[$k], array_rand($otherUserIds, $favCount));

                foreach ($randomIds as $favUserId) {
                    $key = $listing->id.'-'.$favUserId;
                    if (! in_array($key, $alreadyFavorited, true)) {
                        $favoriteInserts[] = [
                            'listing_id' => $listing->id,
                            'user_id' => $favUserId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $alreadyFavorited[] = $key;
                    }
                }
            }

            if (! empty($favoriteInserts)) {
                ListingFavorite::insert($favoriteInserts);
            }
        }
    }

    private function getListingsData(): array
    {
        return [
            // Active listings - Products with mapped categories
            [
                'title' => 'Professional DSLR Camera Kit',
                'description' => 'Canon EOS 5D Mark IV with 24-70mm f/2.8L lens, extra battery, and carrying case. Perfect for professional photography. Gently used and well maintained.',
                'type_slug' => 'product',
                'category_slug' => 'electronics',
                'pricing_mode' => 'fixed',
                'price' => 2500.00,
                'city' => 'New York',
                'area' => 'Manhattan',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => true,
                'is_verified' => true,
            ],
            [
                'title' => 'Electric Scooter - Eco Friendly',
                'description' => 'Xiaomi Mi Electric Scooter 3. Range up to 30km, max speed 25km/h. Includes charger and user manual. Great condition, used only 3 months.',
                'type_slug' => 'product',
                'category_slug' => 'vehicles',
                'pricing_mode' => 'fixed',
                'price' => 450.00,
                'city' => 'Los Angeles',
                'area' => 'Downtown',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => false,
                'is_verified' => true,
            ],
            [
                'title' => 'Gaming Laptop - RTX 4070',
                'description' => 'ASUS ROG Strix G16 with Intel i9-13900H, 32GB RAM, 1TB SSD, NVIDIA RTX 4070. Like new condition, used for only 2 months. Original box included.',
                'type_slug' => 'product',
                'category_slug' => 'electronics',
                'pricing_mode' => 'fixed',
                'price' => 1800.00,
                'city' => 'San Francisco',
                'area' => 'SOMA',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => true,
                'is_verified' => true,
            ],
            [
                'title' => 'Vintage Wooden Dining Table',
                'description' => 'Beautiful solid oak dining table from 1950s. Seats 8-10 people. Restored professionally, perfect condition. Dimensions: 96x42x30 inches.',
                'type_slug' => 'product',
                'category_slug' => 'home-gardening',
                'pricing_mode' => 'fixed',
                'price' => 1200.00,
                'city' => 'Chicago',
                'area' => 'Lincoln Park',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => false,
                'is_verified' => false,
            ],
            [
                'title' => 'Mountain Bike - Trek X-Caliber 9',
                'description' => 'Trek X-Caliber 9 hardtail mountain bike. Size L, 29" wheels, Shimano XT groupset. Recently serviced, new tires. Perfect for trail riding.',
                'type_slug' => 'product',
                'category_slug' => 'vehicles',
                'pricing_mode' => 'fixed',
                'price' => 850.00,
                'city' => 'Denver',
                'area' => 'Boulder',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => false,
                'is_verified' => true,
            ],
            [
                'title' => 'Drone with 4K Camera',
                'description' => 'DJI Mavic Air 2 Fly More Combo. Includes 3 batteries, controller, ND filters, charging hub, carrying bag. 4K/60fps video, 48MP photos.',
                'type_slug' => 'product',
                'category_slug' => 'electronics',
                'pricing_mode' => 'fixed',
                'price' => 750.00,
                'city' => 'Seattle',
                'area' => 'Capitol Hill',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => true,
                'is_verified' => false,
            ],
            // Service listings with mapped categories
            [
                'title' => 'Professional Home Cleaning Service',
                'description' => 'Thorough home cleaning by experienced team. Includes dusting, vacuuming, mopping, kitchen and bathroom deep clean. Eco-friendly products used.',
                'type_slug' => 'service',
                'category_slug' => 'property-spaces',
                'pricing_mode' => 'hourly',
                'price' => 35.00,
                'city' => 'New York',
                'area' => 'Brooklyn',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => false,
                'is_verified' => true,
            ],
            [
                'title' => 'Wedding Photography Package',
                'description' => 'Full day wedding photography coverage. Includes 2 photographers, 500+ edited photos, online gallery, and 8x10 photo album. 10 years of experience.',
                'type_slug' => 'service',
                'category_slug' => 'event-wedding',
                'pricing_mode' => 'fixed',
                'price' => 2500.00,
                'city' => 'Miami',
                'area' => 'South Beach',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => true,
                'is_verified' => true,
            ],
            [
                'title' => 'Same-Day Package Delivery',
                'description' => 'Fast and reliable same-day delivery within city limits. Tracked service with insurance up to $500. Available 7 days a week.',
                'type_slug' => 'service',
                'category_slug' => 'vehicles',
                'pricing_mode' => 'fixed',
                'price' => 15.00,
                'city' => 'Austin',
                'area' => 'Downtown',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => false,
                'is_verified' => false,
            ],
            [
                'title' => 'Math Tutor - High School & College',
                'description' => 'Expert tutoring in Algebra, Calculus, Statistics, and Linear Algebra. PhD student with 5+ years teaching experience.',
                'type_slug' => 'service',
                'category_slug' => 'tools-equipment',
                'pricing_mode' => 'hourly',
                'price' => 50.00,
                'city' => 'Boston',
                'area' => 'Cambridge',
                'status' => Listing::STATUS_ACTIVE,
                'is_featured' => false,
                'is_verified' => true,
            ],
            // Draft listing
            [
                'title' => 'Vintage Record Player Collection',
                'description' => 'Collection of vintage record players from 1960s-1980s. All in working condition. Includes 50+ vinyl records.',
                'type_slug' => 'product',
                'category_slug' => 'electronics',
                'pricing_mode' => 'fixed',
                'price' => 600.00,
                'city' => 'Portland',
                'area' => 'Pearl District',
                'status' => Listing::STATUS_DRAFT,
                'is_featured' => false,
                'is_verified' => false,
            ],
            // Pending listing
            [
                'title' => 'Handmade Artisan Coffee Table',
                'description' => 'Handcrafted coffee table made from reclaimed barn wood. Epoxy river finish. Custom dimensions available.',
                'type_slug' => 'product',
                'category_slug' => 'home-gardening',
                'pricing_mode' => 'fixed',
                'price' => 450.00,
                'city' => 'Nashville',
                'area' => 'Downtown',
                'status' => Listing::STATUS_PENDING,
                'is_featured' => false,
                'is_verified' => false,
            ],
            // Expired listing
            [
                'title' => 'iPhone 15 Pro Max - 256GB',
                'description' => 'iPhone 15 Pro Max in Titanium Blue. 256GB storage, A17 Pro chip. Includes original box, charger, and case. Excellent condition.',
                'type_slug' => 'product',
                'category_slug' => 'electronics',
                'pricing_mode' => 'fixed',
                'price' => 950.00,
                'city' => 'Dallas',
                'area' => 'Uptown',
                'status' => Listing::STATUS_ARCHIVED,
                'is_featured' => true,
                'is_verified' => true,
            ],
        ];
    }
}
