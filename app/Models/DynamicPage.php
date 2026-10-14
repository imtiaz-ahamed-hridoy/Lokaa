<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPage extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'page_title',
        'page_slug',
        'page_content',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
