<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    protected $primaryKey = 'id';
    protected $fillable =
    [
        'file',
    ];

    protected $table = "audios";

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
