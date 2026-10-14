<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use App\Repositories\GroupRepository;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\MessageRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces to their implementations
        $this->app->bind(ConversationRepositoryInterface::class, ConversationRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limit for sending messages: 30 per minute per user
        RateLimiter::for('messages', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many messages sent. Please wait before sending another message.',
                        'errors' => null,
                    ], 429);
                });
        });

        // Rate limit for creating conversations: 10 per minute per user
        RateLimiter::for('conversations', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many conversations created. Please wait.',
                        'errors' => null,
                    ], 429);
                });
        });

        // Rate limit for creating/updating groups: 5 per minute per user
        RateLimiter::for('groups', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many group operations. Please wait.',
                        'errors' => null,
                    ], 429);
                });
        });

        // Rate limit for typing indicators: 60 per minute per user per conversation
        RateLimiter::for('typing', function (Request $request) {
            // Route uses implicit model binding, so route('conversation') returns a model instance
            $conversationId = $request->route('conversation');
            if ($conversationId instanceof Conversation) {
                $conversationId = $conversationId->id;
            }

            return Limit::perMinute(60)
                ->by($request->user()?->id.'|'.$conversationId)
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many typing events. Please slow down.',
                        'errors' => null,
                    ], 429);
                });
        });

        // General API rate limit for reads: 100 per minute per user
        RateLimiter::for('api-read', function (Request $request) {
            return Limit::perMinute(100)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please try again later.',
                        'errors' => null,
                    ], 429);
                });
        });
    }
}
