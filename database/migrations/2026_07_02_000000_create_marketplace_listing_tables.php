<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('listing_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_type_id')->constrained('listing_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();
            $table->timestamps();

            $table->unique(['listing_type_id', 'slug']);
            $table->index(['listing_type_id', 'status']);
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_type_id')->constrained('listing_types')->restrictOnDelete();
            $table->foreignId('listing_category_id')->constrained('listing_categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->enum('pricing_mode', ['fixed', 'hourly', 'daily']);
            $table->decimal('price', 12, 2);
            $table->string('country')->nullable()->index();
            $table->string('city')->index();
            $table->string('area')->nullable()->index();
            $table->string('street_address')->nullable();
            $table->enum('status', ['Draft', 'Pending', 'Active', 'Rejected', 'Archived'])->default('Pending')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_verified')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['listing_type_id', 'listing_category_id', 'status']);
            $table->index(['price', 'created_at']);

            $table->index(['status', 'is_featured', 'created_at']);
            $table->index(['status', 'price'], 'listings_status_price_index');
            $table->index(['listing_type_id', 'listing_category_id', 'status', 'price'], 'listings_type_category_status_price_index');
            $table->index(['status', 'country'], 'listings_status_country_index');
            $table->index(['status', 'listing_type_id', 'price'], 'listings_status_type_price_index');
            $table->index(['status', 'title', 'slug'], 'idx_listings_search_active_title');
            $table->index(['status', 'country', 'city'], 'idx_listings_search_active_location');
            $table->index(['is_featured', 'status'], 'idx_listings_search_featured');
            // Fulltext index is only supported by MySQL/MariaDB, not SQLite
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'slug', 'description'], 'idx_listings_fulltext_search');
            }
        });

        Schema::create('listing_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('listing_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('listing_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['listing_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_favorites');
        Schema::dropIfExists('listing_views');
        Schema::dropIfExists('listing_media');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('listing_categories');
        Schema::dropIfExists('listing_types');
    }
};
