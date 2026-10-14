<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOnline implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $userName,
        public ?string $avatar = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('online-users'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.online';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'avatar' => $this->avatar,
            'last_seen' => now()->toIso8601String(),
        ];
    }
}
