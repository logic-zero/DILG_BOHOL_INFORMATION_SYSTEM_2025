<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincial_Official extends Model
{
    /** @use HasFactory<\Database\Factories\ProvincialOfficialFactory> */
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable =
    [
        'profile_image',
        'name',
        'position',
    ];

    protected $table = "provincial_officials";

    protected $casts = [
        'created_at' => 'datetime',

    ];
}
