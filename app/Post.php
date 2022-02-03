<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'id_page',
        'created_time',
        'message',
        'story'   ,
        'full_picture'    ,
        'is_published'    ,
        'type',
        'scheduled_publish_time'
    ];
}
