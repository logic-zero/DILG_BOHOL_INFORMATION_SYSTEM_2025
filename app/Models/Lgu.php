<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lgu extends Model
{
    use HasFactory;


    protected $primaryKey = 'id';
    protected $fillable =
    [
        'municipality_id',
        'mayor',
        'vice_mayor',
        'sb_member1',
        'sb_member2',
        'sb_member3',
        'sb_member4',
        'sb_member5',
        'sb_member6',
        'sb_member7',
        'sb_member8',
        'sb_member9',
        'sb_member10',
        'lb_pres',
        'psk_pres'

    ];

    protected $table = "lgus";


    protected $casts = [
        'created_at' => 'datetime',

    ];

    public function municipality() {
        return $this->belongsTo('App\Models\Municipality');

    }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //     ->logOnly(['municipality_id','mayor',
    //                 'vice_mayor','sb_member1',
    //                 'sb_member2','sb_member3',
    //                 'sb_member4','sb_member5',
    //                 'sb_member6','sb_member7',
    //                 'sb_member8','sb_member9',
    //                 'sb_member10','lb_pres',
    //                 'psk_pres',])
    //     ->setDescriptionForEvent(fn(string $eventName) => "A Municipal Official has been {$eventName}")
    //     ->logOnlyDirty();
    // }

}
