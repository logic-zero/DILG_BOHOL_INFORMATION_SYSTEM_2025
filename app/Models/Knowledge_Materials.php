<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Knowledge_Materials extends Model
{
    protected $primaryKey = 'id';
    protected $fillable =
    [
        'title',
        'file',
        'date'

    ];

    protected $table = "knowledge_materials";


    protected $casts = [
        'created_at' => 'datetime',
        'date' => 'date',

    ];
}
