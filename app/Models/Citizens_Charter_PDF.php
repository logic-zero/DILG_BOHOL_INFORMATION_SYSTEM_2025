<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citizens_Charter_PDF extends Model
{
    protected $primaryKey = 'id';
    protected $fillable =
    [
        'file',

    ];

    protected $table = "citizens_charters_pdf";


    protected $casts = [
        'created_at' => 'datetime',

    ];
}
