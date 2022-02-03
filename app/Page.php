<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'id',
        'name',
        'access_token',
        'image'
    ];
}
