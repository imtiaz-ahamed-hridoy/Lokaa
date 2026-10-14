<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    public const STATUS_DRAFT = 'Draft';

    public const STATUS_PENDING = 'Pending';

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_REJECTED = 'Rejected';

    public const STATUS_ARCHIVED = 'Archived';

    public const PRICING_FIXED = 'fixed';

    public const PRICING_HOURLY = 'hourly';

    public const PRICING_DAILY = 'daily';

    protected $fillable = [
        'user_id',
        'listing_type_id',
        'listing_category_id',
        'title',
        'slug',
        'description',
        'pricing_mode',
        'price',
        'country',
        'city',
        'area',
        'street_address',
        'status',
        'published_at',
        'rejected_at',
        'is_featured',
        'is_verified',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
        'published_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ListingType::class, 'listing_type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ListingCategory::class, 'listing_category_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ListingMedia::class)->orderBy('id');
    }

    public function coverMedia(): HasOne
    {
        return $this->hasOne(ListingMedia::class)->oldest('id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ListingView::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ListingFavorite::class);
    }

    public function ownerReviews(): HasMany
    {
        return $this->hasMany(UserReview::class, 'reviewee_id', 'user_id');
    }

    public function wishlists(): HasMany
    {
        return $this->favorites();
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeStatus($query, ?string $status)
    {
        return $query->when($status, fn ($query) => $query->where('status', $status));
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeWithWishlistStatus($query, ?User $user = null)
    {
        if (! $user) {
            return $query;
        }

        return $query->withExists([
            'favorites as is_wishlisted' => fn ($q) => $q->where('user_id', $user->id),
        ]);
    }

    public function loadWishlistStatus(?User $user = null): static
    {
        if (! $user) {
            return $this;
        }

        return $this->loadExists([
            'favorites as is_wishlisted' => fn ($q) => $q->where('user_id', $user->id),
        ]);
    }

    public function scopeMarketplaceSearch($query, ?string $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    public function getPriceDisplayAttribute(): string
    {
        $suffix = match ($this->pricing_mode) {
            self::PRICING_HOURLY => '/hr',
            self::PRICING_DAILY => '/day',
            default => '',
        };

        return '$'.rtrim(rtrim(number_format((float) $this->price, 2, '.', ''), '0'), '.').$suffix;
    }

    public function getLocationDisplayAttribute(): string
    {
        return collect([$this->area, $this->city, $this->country])->filter()->implode(', ');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where(is_numeric($value) ? 'id' : 'slug', $value)->first();
    }
}
