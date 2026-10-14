<?php

namespace App\Models;

use App\Traits\ImagePathTrait;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeProcessItem extends Model
{
    use ImagePathTrait;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'icon',
        'title',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'icon',
    ];

    protected $appends = [
        'icon_url',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->fullImageUrl($this->icon);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeOrdered($query)
    {
        return $query->latest('id');
    }
}
