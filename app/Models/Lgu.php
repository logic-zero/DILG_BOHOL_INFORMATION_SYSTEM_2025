<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lgu extends Model
{
    use HasFactory, LogsActivity;

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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has successfully added a new LGU entry for municipality ID '{$model->municipality_id}'.");
        });

        static::updated(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has updated the LGU entry for municipality ID '{$model->municipality_id}'.");
        });

        static::deleted(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has deleted the LGU entry for municipality ID '{$model->municipality_id}'.");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('lgu')
            ->setDescriptionForEvent(fn(string $eventName) => "LGU entry has been {$eventName} successfully.");
    }
}
