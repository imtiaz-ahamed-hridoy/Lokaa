<?php

namespace App\Models;

use App\Traits\ImagePathTrait;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeHeroSection extends Model
{
    use ImagePathTrait;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'badge',
        'heading',
        'description',
        'background_image',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'background_image',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected $appends = [
        'background_image_url',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->fullImageUrl($this->background_image);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
