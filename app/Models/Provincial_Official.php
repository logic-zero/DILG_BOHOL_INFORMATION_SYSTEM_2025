<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Provincial_Official extends Model
{
    use HasFactory, LogsActivity;

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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has successfully added a new Provincial Official: '{$model->name}', Position: '{$model->position}'.");
        });

        static::updated(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has updated the Provincial Official: '{$model->name}', Position: '{$model->position}'.");
        });

        static::deleted(function ($model) {
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';
            activity()
                ->performedOn($model)
                ->causedBy($user)
                ->log("The user '{$userName}' has deleted the Provincial Official: '{$model->name}', Position: '{$model->position}'.");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('provincial_official')
            ->setDescriptionForEvent(fn(string $eventName) => "Provincial Official entry has been {$eventName} successfully.");
    }
}
