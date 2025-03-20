<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oragnizational_Structure extends Model
{
    protected $primaryKey = 'id';
    protected $fillable =
    [
        'profile_img',
        'fname',
        'mid_initial',
        'lname',
        'position',
        'management_and_administrative_roles',

    ];

    protected $table = "organizational_structure";


    protected $casts = [
        'created_at' => 'datetime',

    ];
}
