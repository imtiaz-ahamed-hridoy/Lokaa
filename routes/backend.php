<?php

use App\Http\Controllers\Web\Admin\Contact\ContactController;
use App\Http\Controllers\Web\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Web\Admin\DynamicPage\DynamicPageController;
use App\Http\Controllers\Web\Admin\HomeCMS\CtaBannerController;
use App\Http\Controllers\Web\Admin\HomeCMS\FeaturedServicesConfigController;
use App\Http\Controllers\Web\Admin\HomeCMS\HeroSectionController;
use App\Http\Controllers\Web\Admin\HomeCMS\HotDealsConfigController;
use App\Http\Controllers\Web\Admin\HomeCMS\ProcessSectionController;
use App\Http\Controllers\Web\Admin\Marketplace\ListingCategoryController;
use App\Http\Controllers\Web\Admin\Marketplace\ListingController as AdminListingController;
use App\Http\Controllers\Web\Admin\Marketplace\ListingTypeController;
use App\Http\Controllers\Web\Admin\Marketplace\ReviewController;
use App\Http\Controllers\Web\Admin\Newsletter\NewsletterController;
use App\Http\Controllers\Web\Admin\SystemLog\SystemLogController;
use App\Http\Controllers\Web\Admin\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Home CMS Routes
Route::prefix('home-cms')->name('home-cms.')->group(function () {
    // Hero Section (edit only)
    Route::patch('/hero/{homeHeroSection}/status', [HeroSectionController::class, 'updateStatus'])->name('hero.updateStatus');
    Route::resource('/hero', HeroSectionController::class)->parameters(['hero' => 'homeHeroSection'])->only(['index', 'edit', 'update']);

    // Process Section (edit only)
    Route::patch('/process/{homeProcessItem}/status', [ProcessSectionController::class, 'updateStatus'])->name('process.updateStatus');
    Route::resource('/process', ProcessSectionController::class)->parameters(['process' => 'homeProcessItem'])->only(['index', 'edit', 'update']);

    // Hot Deals
    Route::patch('/hot-deals/{homeHotDealsConfig}/status', [HotDealsConfigController::class, 'updateStatus'])->name('hot-deals.updateStatus');
    Route::resource('/hot-deals', HotDealsConfigController::class)->parameters(['hot-deals' => 'homeHotDealsConfig'])->only(['index', 'edit', 'update']);

    // Featured Services
    Route::patch('/featured-services/{homeFeaturedServicesConfig}/status', [FeaturedServicesConfigController::class, 'updateStatus'])->name('featured-services.updateStatus');
    Route::resource('/featured-services', FeaturedServicesConfigController::class)->parameters(['featured-services' => 'homeFeaturedServicesConfig'])->only(['index', 'edit', 'update']);

    // CTA Banner
    Route::patch('/cta-banner/{homeCtaBanner}/status', [CtaBannerController::class, 'updateStatus'])->name('cta-banner.updateStatus');
    Route::resource('/cta-banner', CtaBannerController::class)->parameters(['cta-banner' => 'homeCtaBanner'])->only(['index', 'edit', 'update']);
});

Route::patch('/listing-type/{listingType}/status', [ListingTypeController::class, 'updateStatus'])->name('listing-type.updateStatus');
Route::resource('/listing-type', ListingTypeController::class)->parameters(['listing-type' => 'listingType']);

Route::patch('/listing-category/{listingCategory}/status', [ListingCategoryController::class, 'updateStatus'])->name('listing-category.updateStatus');
Route::resource('/listing-category', ListingCategoryController::class)->parameters(['listing-category' => 'listingCategory']);

Route::patch('/listing/{listing}/status', [AdminListingController::class, 'updateStatus'])->name('listing.updateStatus');
Route::patch('/listing/{listing}/restore', [AdminListingController::class, 'restore'])->name('listing.restore');
Route::resource('/listing', AdminListingController::class);
Route::resource('/review', ReviewController::class)->only(['index', 'show', 'destroy']);

Route::get('/user/export-csv', [UserController::class, 'exportCsv'])->name('user.exportCsv');
Route::patch('/user/{user}/status', [UserController::class, 'updateStatus'])->name('user.updateStatus');
Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
Route::resource('/user', UserController::class);

Route::patch('/dynamic_page/{dynamicPage}/status', [DynamicPageController::class, 'updateStatus'])->name('dynamic_page.updateStatus');
Route::resource('/dynamic_page', DynamicPageController::class);

Route::resource('/contact', ContactController::class)->only(['index', 'show', 'destroy']);

Route::resource('/newsletter', NewsletterController::class)->only(['index', 'destroy']);

Route::delete('/system-log/bulk-destroy', [SystemLogController::class, 'bulkDestroy'])->name('system-log.bulkDestroy');
Route::delete('/system-log/clear-all', [SystemLogController::class, 'clearAll'])->name('system-log.clearAll');
Route::resource('/system-log', SystemLogController::class)->only(['index', 'destroy']);
