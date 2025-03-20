<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $primaryKey = 'id';
    protected $fillable =
        [
            'user_id',
            'hiring_img',
            'position',
            'details',
            'link',
            'remarks'
        ];

    protected $table = "job_vacancies";

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
