<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_featured_services_configs', function (Blueprint $table) {
            $table->foreignId('listing_type_id')
                ->nullable()
                ->after('title')
                ->constrained('listing_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('home_featured_services_configs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('listing_type_id');
        });
    }
};
