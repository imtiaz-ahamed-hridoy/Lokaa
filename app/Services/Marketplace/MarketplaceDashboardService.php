<?php

namespace App\Services\Marketplace;

use App\Http\Resources\Marketplace\MarketplaceDashboardRecentMessagesResource;
use App\Http\Resources\Marketplace\MarketplaceDashboardResource;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarketplaceDashboardService
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function forUser(User $user): array
    {
        $base = Listing::query()->ownedBy($user);

        $counts = (clone $base)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $recent = (clone $base)
            ->with(['type:id', 'category:id', 'coverMedia'])
            ->latest('id')
            ->get();

        // Get recent conversations for the user (for dashboard messages widget)
        $recentConversations = Conversation::whereUserIsMember($user->id)
            ->with([
                'lastMessage.sender',
                'members.user',
                'creator',
            ])
            ->whereNotNull('last_message_at')
            ->orderBy('last_message_at', 'desc')
            ->take(8)
            ->get();

        return [
            'greeting' => [
                'date' => Carbon::now()->format('l - M j, Y'),
                'text' => $this->getGreeting().', '.$user->name,
            ],

            'stats' => [
                'active_listings' => (int) ($counts[Listing::STATUS_ACTIVE] ?? 0),
                'total_listings' => (int) $counts->sum(),
                'pending_listings' => (int) ($counts[Listing::STATUS_PENDING] ?? 0),
                'draft_listings' => (int) ($counts[Listing::STATUS_DRAFT] ?? 0),
                'rejected_listings' => (int) ($counts[Listing::STATUS_REJECTED] ?? 0),
                'archived_listings' => (int) ($counts[Listing::STATUS_ARCHIVED] ?? 0),
                'total_inquiries' => 0,
                'unread_messages' => $this->conversationRepository->getUnreadCount($user->id),
            ],

            'recent_listings' => MarketplaceDashboardResource::collection($recent),

            'recent_messages' => MarketplaceDashboardRecentMessagesResource::collection($recentConversations),
        ];
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            $hour < 21 => 'Good evening',
            default => 'Good night',
        };
    }
}
