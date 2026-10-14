<?php

namespace App\Models;

use App\Traits\ImagePathTrait;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListingCategory extends Model
{
    use HasFactory;
    use ImagePathTrait;
    use Searchable;

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    protected $fillable = [
        'listing_type_id',
        'name',
        'slug',
        'image',
        'status',
    ];

    protected $hidden = [
        'image',
    ];

    protected $appends = [
        'image_url',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ListingType::class, 'listing_type_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->fullImageUrl($this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
