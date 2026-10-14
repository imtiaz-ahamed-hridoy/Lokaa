<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use Searchable;

    protected $fillable = ['email'];
}
