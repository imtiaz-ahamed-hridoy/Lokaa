<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\ImagePathTrait;
use App\Traits\Searchable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, ImagePathTrait, Notifiable, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'location',
        'avatar',
        'password',
        'role',
        'status',
        'reset_code',
        'reset_code_expires_at',
        'provider',
        'provider_id',
        'provider_token',
        'phone',
        'agree_to_terms',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'agree_to_terms' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'reset_code',
        'reset_code_expires_at',
    ];

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? $this->fullImageUrl($this->avatar) : $this->fullImageUrl('uploads/user/default.png');
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function receivedReviews()
    {
        return $this->hasMany(UserReview::class, 'reviewee_id');
    }

    public function writtenReviews()
    {
        return $this->hasMany(UserReview::class, 'reviewer_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }
}
