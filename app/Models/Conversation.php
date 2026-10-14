<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'image',
        'created_by',
        'last_message_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ConversationMember::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        if ($this->isPrivate()) {
            return asset('images/default-avatar.png');
        }

        return asset('images/default-group.png');
    }

    public function scopeWhereUserIsMember($query, int $userId)
    {
        return $query->whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    protected static function booted(): void
    {
        static::creating(function (Conversation $conversation) {
            if ($conversation->isGroup() && empty($conversation->name)) {
                $conversation->name = 'New Group';
            }
        });
    }
}
