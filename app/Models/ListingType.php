<?php

namespace App\Models;

use App\Traits\ImagePathTrait;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListingType extends Model
{
    use HasFactory;
    use ImagePathTrait;
    use Searchable;

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'status',
    ];

    protected $hidden = [
        'image',
    ];

    protected $appends = [
        'image_url',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(ListingCategory::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->fullImageUrl($this->image);
    }
}
