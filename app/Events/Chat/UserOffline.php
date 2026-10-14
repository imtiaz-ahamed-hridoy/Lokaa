<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOffline implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('online-users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.offline';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'last_seen' => now()->toIso8601String(),
        ];
    }
}
