<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
    // return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    //

});

if (app()->environment('local')) {
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/run-migrate', function () {
            Artisan::call('migrate');

            return 'Database migration successfully!';
        });

        Route::get('/run-migrate-fresh', function () {
            Artisan::call('migrate:fresh');

            return 'Database migration fresh successfully!';
        });

        Route::get('/run-seed', function () {
            Artisan::call('db:seed');

            return 'Database seeding completed successfully!';
        });

        Route::get('/clear-config', function () {
            Artisan::call('config:clear');
            Artisan::call('optimize:clear');

            return 'Config cache cleared successfully!';
        });

        Route::get('/storage-link', function () {
            Artisan::call('storage:link');

            return 'Storage link created successfully!';
        });
    });
}

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
