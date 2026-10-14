<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\SocialLoginController;
use App\Http\Controllers\API\Chat\ConversationController;
use App\Http\Controllers\API\Chat\GroupController;
use App\Http\Controllers\API\Chat\MessageController;
use App\Http\Controllers\API\Contact\ContactController;
use App\Http\Controllers\API\DynamicPage\DynamicPageController;
use App\Http\Controllers\API\HeroSearch\HeroSearchController;
use App\Http\Controllers\API\HomeCMS\HomeController;
use App\Http\Controllers\API\HomeCMS\HomeCtaBannerController;
use App\Http\Controllers\API\HomeCMS\HomeExploreCategoryController;
use App\Http\Controllers\API\HomeCMS\HomeFeaturedServicesController;
use App\Http\Controllers\API\HomeCMS\HomeHeroController;
use App\Http\Controllers\API\HomeCMS\HomeHotDealsController;
use App\Http\Controllers\API\HomeCMS\HomeProcessController;
use App\Http\Controllers\API\Marketplace\ListingController;
use App\Http\Controllers\API\Marketplace\ListingLookupController;
use App\Http\Controllers\API\Marketplace\MarketplaceController;
use App\Http\Controllers\API\Marketplace\MarketplaceDashboardController;
use App\Http\Controllers\API\Marketplace\ReviewController;
use App\Http\Controllers\API\Marketplace\WishlistController;
use App\Http\Controllers\API\Newsletter\NewsletterController;
use App\Http\Controllers\API\SystemSetting\SystemSettingController;
use App\Http\Controllers\API\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {

    //  Authentication routes
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/verify_otp', [RegisterController::class, 'verify_otp']);
    Route::post('/resend_otp', [RegisterController::class, 'resend_otp']);
    Route::post('/forgot-password', [RegisterController::class, 'forgot_password']);
    Route::post('/forgot-verify-otp', [RegisterController::class, 'forgot_verify_otp']);
    Route::post('/reset-password', [RegisterController::class, 'reset_password']);
    Route::post('/social-login', [SocialLoginController::class, 'socialLogin']);
});

// system settings
Route::get('/system-info', [SystemSettingController::class, 'systemInfo']);
Route::get('/social-links', [SystemSettingController::class, 'socialLinks']);

// dynamic page
Route::get('/terms-of-service', [DynamicPageController::class, 'termsOfService']);
Route::get('/privacy-policy', [DynamicPageController::class, 'privacyPolicy']);

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);

// contact
Route::post('/contact', [ContactController::class, 'store']);

// Hero Global Search API (dedicated, separate from all other APIs)
Route::get('/search', HeroSearchController::class);

// Home CMS Public API
Route::prefix('home')->name('home.')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/hero', [HomeHeroController::class, 'index']);
    Route::get('/process', [HomeProcessController::class, 'index']);
    Route::get('/explore-categories', [HomeExploreCategoryController::class, 'index']);
    Route::get('/hot-deals', [HomeHotDealsController::class, 'index']);
    Route::get('/featured-services', [HomeFeaturedServicesController::class, 'index']);
    Route::get('/cta-banner', [HomeCtaBannerController::class, 'index']);
});

Route::get('/marketplace', [MarketplaceController::class, 'index']);
Route::get('/marketplace/{listing}', [MarketplaceController::class, 'show'])->whereNumber('listing');

Route::get('/marketplace/lookups/listing-types', [ListingLookupController::class, 'types']);
Route::get('/marketplace/lookups/categories', [ListingLookupController::class, 'categories']);

Route::get('/users/{user}/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/summary/{user}', [ReviewController::class, 'summary']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function ($router) {
    // common routes
    Route::post('/logout', [LoginController::class, 'logout']);

    // profile update routes
    Route::get('/user/profile', [UserController::class, 'userProfile']);
    Route::post('/user/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);
    Route::post('/user/account-delete', [UserController::class, 'accountDelete']);

    Route::prefix('marketplace')->group(function () {
        Route::get('/dashboard', MarketplaceDashboardController::class);
        Route::get('/lookups/listing-types', [ListingLookupController::class, 'types']);
        Route::get('/lookups/categories', [ListingLookupController::class, 'categories']);

        Route::get('/listings', [ListingController::class, 'index']);
        Route::post('/listings', [ListingController::class, 'store']);
        Route::get('/listings/{listing}', [ListingController::class, 'show']);
        Route::match(['put', 'post'], '/listings/{listing}', [ListingController::class, 'update']);
        Route::patch('/listings/{listing}/status', [ListingController::class, 'updateStatus']);
        Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);
    });

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::get('/wishlist/count', [WishlistController::class, 'count']);
    Route::post('/wishlist/{listing}', [WishlistController::class, 'toggle']);

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // =========================================================================
    // MESSAGING SYSTEM ROUTES
    // =========================================================================
    // Rate limiting:
    //   messages:   30 req/min per user (sending/editing/deleting messages)
    //   conversations: 10 req/min per user (creating)
    //   groups:      5 req/min per user (creating/updating)
    //   typing:     60 req/min per user per conversation (approximately 1 per second)
    //   api-read:  100 req/min per user (listing conversations, messages, etc.)

    // Conversations
    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', [ConversationController::class, 'index'])->name('index')->middleware('throttle:api-read');
        Route::post('/', [ConversationController::class, 'store'])->name('store')->middleware('throttle:conversations');
        Route::get('/unread-count', [ConversationController::class, 'unreadCount'])->name('unread-count')->middleware('throttle:api-read');
        Route::get('/{conversation}', [ConversationController::class, 'show'])->name('show')->middleware('throttle:api-read');
        Route::delete('/{conversation}', [ConversationController::class, 'destroy'])->name('destroy')->middleware('throttle:conversations');

        // Messages within a conversation
        Route::get('/{conversation}/messages', [MessageController::class, 'index'])->name('messages.index')->middleware('throttle:api-read');

        // Typing indicators (throttled separately)
        Route::post('/{conversation}/typing', [MessageController::class, 'typing'])->name('typing')->middleware('throttle:typing');
        Route::post('/{conversation}/stopped-typing', [MessageController::class, 'stoppedTyping'])->name('stopped-typing')->middleware('throttle:typing');

        // Mark as read
        Route::post('/{conversation}/read', [MessageController::class, 'markAsRead'])->name('mark-as-read')->middleware('throttle:api-read');
    });

    // Messages (send: 30/min, edit/delete: included in 30/min)
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store')->middleware('throttle:messages');
    Route::put('/messages/{message}', [MessageController::class, 'update'])->name('messages.update')->middleware('throttle:messages');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy')->middleware('throttle:messages');

    // Groups
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::post('/', [GroupController::class, 'store'])->name('store')->middleware('throttle:groups');
        Route::put('/{conversation}', [GroupController::class, 'update'])->name('update')->middleware('throttle:groups');
        Route::delete('/{conversation}', [GroupController::class, 'destroy'])->name('destroy')->middleware('throttle:groups');

        // Group members management
        Route::post('/{conversation}/members', [GroupController::class, 'addMember'])->name('members.add')->middleware('throttle:groups');
        Route::delete('/{conversation}/members/{user}', [GroupController::class, 'removeMember'])->name('members.remove')->middleware('throttle:groups');
        Route::post('/{conversation}/leave', [GroupController::class, 'leave'])->name('leave')->middleware('throttle:groups');
    });

    // Online users
    Route::get('/users/online', function () {
        // The frontend should listen to the presence channel 'online-users'
        // This endpoint simply returns success - online status is real-time via Reverb
        return response()->json([
            'success' => true,
            'message' => 'Use the presence channel "online-users" via WebSocket to get online users in real-time.',
        ]);
    })->name('users.online')->middleware('throttle:api-read');
});
