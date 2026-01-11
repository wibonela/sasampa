<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sanduku extends Model
{
    protected $fillable = [
        'type',
        'title',
        'description',
        'contact',
        'page_url',
        'user_agent',
        'screenshot',
        'status',
    ];
}
