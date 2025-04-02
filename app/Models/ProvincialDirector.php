<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProvincialDirector extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable =
        [
            'message',
        ];

    protected $table = "provincial_directors";


    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
