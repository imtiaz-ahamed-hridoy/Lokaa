<?php

namespace App\Models;

use App\Traits\ImagePathTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingMedia extends Model
{
    use HasFactory;
    use ImagePathTrait;

    protected $fillable = [
        'listing_id',
        'path',
    ];

    protected $appends = [
        'url',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->fullImageUrl($this->path);
    }
}
