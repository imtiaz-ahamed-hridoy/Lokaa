<?php

namespace App\Models;

use App\Traits\ImagePathTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    use HasFactory;
    use ImagePathTrait;

    protected $fillable = [
        'title',
        'email',
        'number',
        'system_name',
        'address',
        'copyright_text',
        'logo',
        'favicon',
        'description',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'logo_url',
        'favicon_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->fullImageUrl($this->logo);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->fullImageUrl($this->favicon);
    }

    protected static function booted()
    {
        static::updating(function ($systemSetting) {
            if ($systemSetting->isDirty('favicon')) {
                $oldFavicon = $systemSetting->getOriginal('favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
            }

            if ($systemSetting->isDirty('logo')) {
                $oldLogo = $systemSetting->getOriginal('logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }
        });
    }
}
