<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_featured_services_configs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('number_of_listings')->default(8);
            $table->enum('listing_source', ['featured', 'newest', 'popular', 'manual'])->default('featured');
            $table->foreignId('listing_category_id')->nullable()->constrained('listing_categories')->nullOnDelete();
            $table->json('manual_listing_ids')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_featured_services_configs');
    }
};
