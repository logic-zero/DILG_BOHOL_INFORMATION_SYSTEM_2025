<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citizens_Charter extends Model
{
    protected $primaryKey = 'id';
    protected $fillable =
    [
       'title',
       'file',
       'thumbnail',
    ];

    protected $table = "citizens_charters";


    protected $casts = [
        'created_at' => 'datetime',

    ];
}
