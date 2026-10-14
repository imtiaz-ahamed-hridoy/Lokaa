<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_cta_banners', function (Blueprint $table) {
            $table->id();
            $table->string('background_image')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_cta_banners');
    }
};
