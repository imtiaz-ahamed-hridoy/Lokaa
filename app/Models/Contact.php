<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use Searchable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'topic',
        'message',
    ];
}
